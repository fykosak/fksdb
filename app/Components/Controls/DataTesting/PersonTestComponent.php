<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\DataTesting;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\DataTesting\Tests\ModelPerson\PersonTest;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

class PersonTestComponent extends BaseComponent
{

    /**
     * @persistent
     */
    public ?int $startId = 0;
    /**
     * @persistent
     */
    public ?int $endId = 0;
    /**
     * @var PersonTest[]|null
     * @persistent
     */
    public ?array $tests = [];
    /**
     * @persistent
     * @var string[]|null
     */
    public ?array $levels = [];

    private PersonService $personService;
    private DataTestingFactory $dataTestingFactory;

    final public function injectPrimary(PersonService $personService, DataTestingFactory $dataTestingFactory): void
    {
        $this->personService = $personService;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('start_id', sprintf(_('From %s'), 'person_id'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->startId);
        $form->addText('end_id', sprintf(_('To %s'), 'person_id'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->endId);
        $levelsContainer = new ContainerWithOptions($this->container);
        $levelsContainer->setOption('label', _('Level'));

        foreach (TestLog::getAvailableLevels() as $level) {
            $field = $levelsContainer->addCheckbox($level, _($level));
            if (\in_array($level, $this->levels)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($levelsContainer, 'levels');

        $testsContainer = new ContainerWithOptions($this->container);
        $testsContainer->setOption('label', _('Tests'));
        foreach ($this->dataTestingFactory->getTests('person') as $key => $test) {
            $field = $testsContainer->addCheckbox((string)$key, $test->title);
            if (\in_array($test, $this->tests)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($testsContainer, 'tests');

        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form) {
            /** @phpstan-var array{
             *     levels:array<string,bool>,
             *     tests:array<string,bool>,
             *     start_id:int,
             *     end_id:int
             * } $values
             */
            $values = $form->getValues('array');
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
     * @phpstan-return  array<int,array{model:PersonModel,log:Message[]}>
     */
    private function calculateProblems(): array
    {
        $query = $this->personService->getTable()->where('person_id BETWEEN ? AND ?', $this->startId, $this->endId);
        $logs = [];
        /** @var PersonModel $model */
        foreach ($query as $model) {
            $logger = new MemoryLogger();
            foreach ($this->tests as $test) {
                $test->run($logger, $model);
            }
            $personLog = \array_filter(
                $logger->getMessages(),
                fn(Message $simpleLog): bool => \in_array($simpleLog->level, $this->levels)
            );
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }
        return $logs;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', ['logs' => $this->calculateProblems()]);
    }
}
