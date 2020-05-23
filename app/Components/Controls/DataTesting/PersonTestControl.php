<?php

namespace FKSDB\Components\Controls\DataTesting;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\DataTesting\Tests\Person\PersonTest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Templating\FileTemplate;

/**
 * Class PersonTestControl
 * @package FKSDB\Components\Controls\DataTesting
 * @property-read FileTemplate $template
 */
class PersonTestControl extends BaseComponent {

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
     * @var PersonTest[]
     * @persistent
     */
    public $tests = [];

    /**
     * @var array
     * @persistent
     */
    public $levels = [];
    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var PersonTest[]
     */
    private $availableTests = [];

    /**
     * ValidationControl constructor.
     * @param Container $container
     * @param PersonTest[] $availableTests
     */
    public function __construct(Container $container, array $availableTests) {
        parent::__construct($container);
        $this->availableTests = $availableTests;
    }

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectPrimary(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
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

        foreach (TestLog::getAvailableLevels() as $level) {
            $field = $levelsContainer->addCheckbox($level, _($level));
            if (\in_array($level, $this->levels)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($levelsContainer, 'levels');

        $testsContainer = new ContainerWithOptions();
        $testsContainer->setOption('label', _('Tests'));
        foreach ($this->availableTests as $key => $test) {
            $field = $testsContainer->addCheckbox($key, $test->getTitle());
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
        $query = $this->servicePerson->getTable()->where('person_id BETWEEN ? AND ?', $this->startId, $this->endId);
        $logs = [];
        /** @var ModelPerson $model */
        foreach ($query as $model) {

            $logger = new TestsLogger();
            foreach ($this->tests as $test) {
                $test->run($logger, $model);
            }
            $personLog = \array_filter($logger->getLogs(), function (TestLog $simpleLog) {
                return \in_array($simpleLog->getLevel(), $this->levels);
            });
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }

        return $logs;
    }

    public function render() {
        $this->template->logs = $this->calculateProblems();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
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
