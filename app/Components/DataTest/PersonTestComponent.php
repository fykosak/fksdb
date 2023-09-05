<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
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
    public ?int $limit = 0;
    /**
     * @phpstan-var array<string,Test<PersonModel>>|null
     * @persistent
     */
    public ?array $tests = [];
    /**
     * @persistent
     * @phpstan-var string[]|null
     */
    public ?array $levels = [];

    private PersonService $personService;
    private DataTestFactory $dataTestFactory;

    final public function injectPrimary(PersonService $personService, DataTestFactory $dataTestFactory): void
    {
        $this->personService = $personService;
        $this->dataTestFactory = $dataTestFactory;
    }

    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('start_id', sprintf(_('From %s'), 'person_id'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->startId);
        $form->addText('limit', sprintf(_('Limit'), 'person_id'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->limit);
        $levelsContainer = new ContainerWithOptions($this->container);
        $levelsContainer->setOption('label', _('Level'));

        foreach ([Message::LVL_ERROR, Message::LVL_WARNING] as $level) {
            $field = $levelsContainer->addCheckbox($level, _($level));
            if (\in_array($level, $this->levels)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($levelsContainer, 'levels');

        $testsContainer = new ContainerWithOptions($this->container);
        $testsContainer->setOption('label', _('Tests'));
        foreach ($this->dataTestFactory->getTests('person') as $key => $test) {
            $field = $testsContainer->addCheckbox($key, $test->title);
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
             *     limit:int
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
                    $this->tests[$testId] = $this->dataTestFactory->getTests('person')[$testId];
                }
            }
            $this->startId = $values['start_id'];
            $this->limit = $values['limit'];
        };
        return $control;
    }

    /**
     * @phpstan-return  array<int,array{model:PersonModel,logs:array<string,Message[]>}>
     */
    private function calculateProblems(): array
    {
        $query = $this->personService->getTable()->limit($this->limit, $this->startId);
        $logs = [];
        /** @var PersonModel $model */
        foreach ($query as $model) {
            $personLog = [];
            foreach ($this->tests as $testId => $test) {
                $logger = new MemoryLogger();
                $test->run($logger, $model);
                $testLog = \array_filter(
                    $logger->getMessages(),
                    fn(Message $simpleLog): bool => \in_array($simpleLog->level, $this->levels)
                );
                if (count($testLog)) {
                    $personLog[$testId] = $testLog;
                }
            }
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'logs' => $personLog];
            }
        }
        return $logs;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR, [
            'tests' => $this->dataTestFactory->getTests('person'),
            'logs' => $this->calculateProblems(),
        ]);
    }
}
