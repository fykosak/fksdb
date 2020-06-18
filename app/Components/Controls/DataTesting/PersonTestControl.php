<?php

namespace FKSDB\Components\Controls\DataTesting;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\DataTesting\DataTestingFactory;
use FKSDB\DataTesting\Tests\Person\PersonTest;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\DataTesting\TestLog;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;

/**
 * Class PersonTestControl
 * @author Michal Červeňák <miso@fykos.cz>
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
     * @var DataTestingFactory
     */
    private $dataTestingFactory;

    /**
     * @param ServicePerson $servicePerson
     * @param DataTestingFactory $dataTestingFactory
     * @return void
     */
    public function injectPrimary(ServicePerson $servicePerson, DataTestingFactory $dataTestingFactory) {
        $this->servicePerson = $servicePerson;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentForm() {
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
        foreach ($this->dataTestingFactory->getTests('person') as $key => $test) {
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
            foreach ($values['levels'] as $level => $value) {
                if ($value) {
                    $this->levels[] = $level;
                }
            }

            $this->tests = [];
            foreach ($values['tests'] as $testId => $value) {
                if ($value) {
                    $this->tests[] = $this->dataTestingFactory->getTests('person')[$testId];
                }
            }
            $this->startId = $values['start_id'];
            $this->endId = $values['end_id'];

        };
        return $control;
    }

    /**
     * @return array[]
     */
    private function calculateProblems(): array {
        $query = $this->servicePerson->getTable()->where('person_id BETWEEN ? AND ?', $this->startId, $this->endId);
        $logs = [];
        /** @var ModelPerson $model */
        foreach ($query as $model) {

            $logger = new MemoryLogger();
            foreach ($this->tests as $test) {
                $test->run($logger, $model);
            }
            $personLog = \array_filter($logger->getMessages(), function (TestLog $simpleLog) {
                return \in_array($simpleLog->getLevel(), $this->levels);
            });
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }

        return $logs;
    }

    /**
     * @return void
     */
    public function render() {
        $this->template->logs = $this->calculateProblems();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
