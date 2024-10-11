<?php

declare(strict_types=1);

namespace FKSDB\Components\Inbox;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskContributionModel;
use FKSDB\Models\ORM\Models\TaskContributionType;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskContributionService;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

final class HandoutFormComponent extends FormComponent
{
    public const TASK_PREFIX = 'task';
    private TaskContributionService $taskContributionService;
    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(
        Container $container,
        ContestYearModel $contestYear,
        int $series
    ) {
        parent::__construct($container);
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    final public function injectPrimary(
        TaskContributionService $taskContributionService
    ): void {
        $this->taskContributionService = $taskContributionService;
    }


    public function render(): void
    {
        $this->setDefaults($this->getForm());
        parent::render();
    }

    public function setDefaults(Form $form): void
    {
        $contributions = [];
        /** @var TaskModel $task */
        foreach ($this->contestYear->getTasks($this->series)->order('tasknr') as $task) {
            $contributions = [
                ...$contributions,
                ...$task->getContributions(
                    TaskContributionType::from(TaskContributionType::GRADE)
                )->fetchAll(),
            ];
        }
        $values = [];
        /** @var TaskContributionModel $contribution */
        foreach ($contributions as $contribution) {
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = [];
            }
            $values[$key][] = $personId;
        }
        $form->setDefaults($values);
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array<string,int[]> $values */
        $values = $form->getValues('array');
        $connection = $this->taskContributionService->explorer->getConnection();
        $connection->beginTransaction();
        /** @var TaskModel $task */
        foreach ($this->contestYear->getTasks($this->series)->order('tasknr') as $task) {
            /** @var TaskContributionModel $contribution */
            foreach (
                $task->getContributions(
                    TaskContributionType::from(TaskContributionType::GRADE)
                ) as $contribution
            ) {
                $this->taskContributionService->disposeModel($contribution);
            }
            $key = self::TASK_PREFIX . $task->task_id;
            foreach ($values[$key] as $personId) {
                $data = [
                    'task_id' => $task->task_id,
                    'person_id' => $personId,
                    'type' => TaskContributionType::GRADE,
                ];
                $this->taskContributionService->storeModel($data);
            }
        }
        $connection->commit();
        $this->getPresenter()->flashMessage(_('Handout saved.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }


    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('save', _('button.save'));
    }

    protected function configureForm(Form $form): void
    {
        $provider = new PersonProvider($this->container);
        $provider->filterOrganizers($this->contestYear->contest);
        /** @var TaskModel $task */
        foreach ($this->contestYear->getTasks($this->series)->order('tasknr') as $task) {
            $control = new PersonSelectBox(
                false,
                $provider,
                $task->label . ' ' . $task->name->getText($this->translator->lang)
            );
            $control->setMultiSelect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }
    }
}
