<?php

namespace FKSDB\Components\Controls\Validation;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Application\UI\Control;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class ValidationControl
 * @package FKSDB\Components\Controls\Validation
 * @property FileTemplate $template
 */
class ValidationControl extends Control {

    /**
     * @var int
     * @persistent
     */
    public $startId = 0;
    /**
     * @var int
     * @persistent
     */
    public $endId = 0;

    /**
     * @var ValidationTest[]
     * @persistent
     */
    public $tests = [];

    /**
     * @var array
     * @persistent
     */
    public $levels = [];

    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var array
     */
    private $availableTests = [];

    /**
     * ValidationControl constructor.
     * @param ServicePerson $servicePerson
     * @param ITranslator $translator
     * @param array $availableTests
     */
    public function __construct(ServicePerson $servicePerson, ITranslator $translator, array $availableTests) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
        $this->translator = $translator;
        $this->availableTests = $availableTests;
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('start_id', _('From person_id'))
            ->addRule(Form::INTEGER)
            ->setDefaultValue($this->startId);
        $form->addText('end_id', _('To person_id'))
            ->addRule(Form::INTEGER)
            ->setDefaultValue($this->endId);
        $levelsContainer = new ContainerWithOptions();
        $levelsContainer->setOption('label', _('Level'));

        foreach (ValidationTest::getAvailableLevels() as $level) {
            $field = $levelsContainer->addCheckbox($level, _($level));
            if (\in_array($level, $this->levels)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($levelsContainer, 'levels');

        $testsContainer = new ContainerWithOptions();
        $testsContainer->setOption('label', _('Tests'));
        foreach ($this->availableTests as $key => $test) {
            $field = $testsContainer->addCheckbox($key, $test::getTitle());
            if (\in_array($test, $this->tests)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($testsContainer, 'tests');

        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->levels = [];
            foreach ($values->levels as $level => $value) {
                if ($value) {
                    $this->levels[] = $level;
                }
            }

            $this->tests = [];
            foreach ($values->tests as $testId => $value) {
                if ($value) {
                    $this->tests[] = $this->availableTests[$testId];
                }
            }
            $this->startId = $values->start_id;
            $this->endId = $values->end_id;

        };
        return $control;
    }

    /**
     * @return array
     */
    private function calculateProblems(): array {
        $query = $this->servicePerson->getTable();

        $query->where('person_id BETWEEN ? AND ?', $this->startId, $this->endId);
        $logs = [];
        foreach ($query as $row) {

            $model = ModelPerson::createFromTableRow($row);
            $log = [];
            foreach ($this->tests as $test) {
                $log[] = $test->run($model);
            }
            $personLog = \array_filter($log, function (ValidationLog $simpleLog) {
                return \in_array($simpleLog->level, $this->levels);
            });
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }

        return $logs;
    }

    public function render() {

        $this->template->logs = $this->calculateProblems();
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ValidationControl.latte');
        $this->template->render();
    }

    /**
     * @param $page
     */
    public function handleChangePage($page) {
        $this->page = $page;
        $this->invalidateControl();

    }
}
