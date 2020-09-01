<?php

namespace FKSDB\Components\Control\AjaxSubmit;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\AbortException;
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

    /**
     * SubmitContainer constructor.
     * @param Container $container
     * @param ModelContestant $contestant
     * @param ModelContest $contest
     * @param int $acYear
     * @param int $year
     */
    public function __construct(Container $container, ModelContestant $contestant, ModelContest $contest, int $acYear, int $year) {
        parent::__construct($container);
        $this->contestant = $contestant;
        $this->contest = $contest;
        $this->acYear = $acYear;
        $this->year = $year;

        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            $this->addComponent(new AjaxSubmit($this->getContext(), $task, $contestant, $acYear), 'task_' . $task->task_id);
        }
    }

    /**
     * @param int|string $name
     * @param bool $throw
     * @return IComponent|null
     * @throws AbortException
     */
    public function getComponent($name, $throw = true) {
        $component = parent::getComponent($name, $throw);
        if (!$component && preg_match('/task_[0-9]+/', $name)) {
            $this->flashMessage(_('Task is not available'), Message::LVL_DANGER);
            $this->redirect('this');
        }
        return $component;
    }

    public function injectPrimary(ServiceTask $serviceTask): void {
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
