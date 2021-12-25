<?php

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\Controls\BaseComponent;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceTask;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

class SubmitContainer extends BaseComponent {

    private ModelContestant $contestant;
    private ServiceTask $serviceTask;

    public function __construct(Container $container, ModelContestant $contestant) {
        parent::__construct($container);
        $this->contestant = $contestant;
        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            $this->addComponent(new AjaxSubmitComponent($this->getContext(), $task, $contestant), 'task_' . $task->task_id);
        }
    }

    protected function createComponent(string $name): ?IComponent {
        $component = parent::createComponent($name);
        if (!$component && preg_match('/task_[0-9]+/', $name)) {
            $this->flashMessage(_('Task is not available'), Message::LVL_ERROR);
            $this->redirect('this');
        }
        return $component;
    }

    final public function injectPrimary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    private function getAvailableTasks(): TypedTableSelection {
        // TODO related
        return $this->serviceTask->getTable()
            ->where('contest_id = ? AND year = ?', $this->contestant->contest_id, $this->contestant->year)
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()')
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }

    final public function render(): void {
        $this->template->availableTasks = $this->getAvailableTasks();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.container.latte');
    }
}
