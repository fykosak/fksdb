<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use Fykosak\NetteORM\TypedSelection;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

class SubmitContainer extends BaseComponent
{

    private ContestantModel $contestant;
    private TaskService $taskService;

    public function __construct(Container $container, ContestantModel $contestant)
    {
        parent::__construct($container);
        $this->contestant = $contestant;
        /** @var TaskModel $task */
        foreach ($this->getAvailableTasks() as $task) {
            $this->addComponent(
                new AjaxSubmitComponent($this->getContext(), $task, $contestant),
                'task_' . $task->task_id
            );
        }
    }

    protected function createComponent(string $name): ?IComponent
    {
        $component = parent::createComponent($name);
        if (!$component && preg_match('/task_[0-9]+/', $name)) {
            $this->flashMessage(_('Task is not available'), Message::LVL_ERROR);
            $this->redirect('this');
        }
        return $component;
    }

    final public function injectPrimary(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    private function getAvailableTasks(): TypedSelection
    {
        // TODO related
        return $this->taskService->getTable()
            ->where('contest_id = ? AND year = ?', $this->contestant->contest_id, $this->contestant->year)
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()')
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }

    final public function render(): void
    {
        $this->template->availableTasks = $this->getAvailableTasks();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.container.latte');
    }
}
