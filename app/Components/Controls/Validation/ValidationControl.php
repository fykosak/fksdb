<?php

namespace FKSDB\Components\Controls\Validation;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;
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
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var ValidationTest[]
     * @persistent
     */
    public $tests = [];
    /**
     * @var array
     */
    private $availableTests = [];
    /**
     * @var array
     * @persistent
     */
    public $levels = [];

    const PERSONS_PER_PAGE = 100;
    /**
     * @var int
     * @persistent
     */
    public $page = 1;
    /**
     * @var ITranslator
     */
    private $translator;

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

        $levelsContainer = new ContainerWithOptions();
        foreach (ValidationTest::getAvailableLevels() as $level) {
            $field = $levelsContainer->addCheckbox($level, _($level));
            if (\in_array($level, $this->levels)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($levelsContainer, 'levels');

        $testsContainer = new ContainerWithOptions();
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

        };
        return $control;
    }

    /**
     * @return array
     */
    private function calculateProblems(): array {

        $query = $this->servicePerson->getTable()->page($this->page, self::PERSONS_PER_PAGE);

        $logs = [];
        foreach ($query as $row) {

            $model = ModelPerson::createFromTableRow($row);
            $personLog = [];
            foreach ($this->tests as $test) {
                Debugger::barDump($test::run($model));
                $log = \array_filter($test::run($model), function (ValidationLog $simpleLog) {
                    return \in_array($simpleLog->level, $this->levels);
                });
                $personLog = \array_merge($personLog, $log);
            }
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }

        return $logs;
    }

    /**
     * @return int
     */
    private function getTotalPage(): int {
        return ($this->servicePerson->getTable()->count() / self::PERSONS_PER_PAGE) + 1;
    }

    public function render() {

        $this->template->logs = $this->calculateProblems();
        $this->template->page = $this->page;
        $this->template->totalPages = $this->getTotalPage();
        $this->template->perPage = self::PERSONS_PER_PAGE;
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
