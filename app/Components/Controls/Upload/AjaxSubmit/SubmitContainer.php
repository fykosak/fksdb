<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Upload\AjaxSubmit;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

class SubmitContainer extends BaseComponent
{

    private ContestantModel $contestant;

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

    private function getAvailableTasks(): TypedGroupedSelection
    {
        return $this->contestant->getContestYear()->getTasks()
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()')
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'container.latte', [
            'availableTasks' => $this->getAvailableTasks(),
        ]);
    }
}
