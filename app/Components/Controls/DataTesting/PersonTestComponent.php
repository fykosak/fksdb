<?php

namespace FKSDB\Components\Controls\DataTesting;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\DataTesting\Tests\ModelPerson\PersonTest;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\DataTesting\TestLog;
use Nette\Forms\Form;

class PersonTestComponent extends BaseComponent {

    /**
     * @persistent
     */
    public ?int $startId = 0;
    /**
     * @persistent
     */
    public ?int $endId = 0;
    /**
     * @var PersonTest[]
     * @persistent
     */
    public ?array $tests = [];
    /**
     * @persistent
     */
    public ?array $levels = [];

    private ServicePerson $servicePerson;
    private DataTestingFactory $dataTestingFactory;

    final public function injectPrimary(ServicePerson $servicePerson, DataTestingFactory $dataTestingFactory): void {
        $this->servicePerson = $servicePerson;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('start_id', sprintf(_('From %s'), 'person_id'))
            ->addRule(Form::INTEGER)
            ->setDefaultValue($this->startId);
        $form->addText('end_id', sprintf(_('To %s'), 'person_id'))
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
            $field = $testsContainer->addCheckbox($key, $test->title);
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
            $personLog = \array_filter($logger->getMessages(), function (TestLog $simpleLog): bool {
                return \in_array($simpleLog->level, $this->levels);
            });
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }

        return $logs;
    }

    final public function render(): void {
        $this->template->logs = $this->calculateProblems();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
