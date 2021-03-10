<?php

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

/**
 * Class SubmitContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitContainer extends BaseComponent {

    private ModelContestant $contestant;
    private ModelContest $contest;
    private int $acYear;
    private int $year;
    private ServiceTask $serviceTask;

    public function __construct(Container $container, ModelContestant $contestant, ModelContest $contest, int $acYear, int $year) {
        parent::__construct($container);
        $this->contestant = $contestant;
        $this->contest = $contest;
        $this->acYear = $acYear;
        $this->year = $year;

        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            $this->addComponent(new AjaxSubmitComponent($this->getContext(), $task, $contestant, $acYear), 'task_' . $task->task_id);
        }
    }

    protected function createComponent(string $name): ?IComponent {
        $component = parent::createComponent($name);
        if (!$component && preg_match('/task_[0-9]+/', $name)) {
            $this->flashMessage(_('Task is not available'), Message::LVL_DANGER);
            $this->redirect('this');
        }
        return $component;

    }

    final public function injectPrimary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    private function getAvailableTasks(): TypedTableSelection {
        return $this->serviceTask->getTable()
            ->where('contest_id = ? AND year = ?', $this->contest->contest_id, $this->year)
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()')
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }

    public function render(): void {
        $this->template->availableTasks = $this->getAvailableTasks();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.container.latte');
        $this->template->render();
    }
}
