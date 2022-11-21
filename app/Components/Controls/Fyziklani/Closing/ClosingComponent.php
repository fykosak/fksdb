<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Closing;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

abstract class ClosingComponent extends BaseComponent
{
    private TeamModel2 $team;
    protected TeamService2 $teamService;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function injectServiceFyziklaniTask(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    final public function handleClose(): void
    {
        $sum = $this->close();

        $this->getPresenter()->flashMessage(
            \sprintf(_('Team "%s" has successfully closed submitting, with total %d points.'), $this->team->name, $sum),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list', ['id' => null]);
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->task = $this->getNextTask();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    /**
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string
    {
        $submits = $this->team->getNonRevokedSubmits()->count('*');
        $tasksOnBoard = $this->team->event->getFyziklaniGameSetup()->tasks_on_board;
        /** @var TaskModel|null $nextTask */
        $nextTask = $this->team->event
            ->getFyziklaniTasks()
            ->order('label')
            ->limit(1, $submits + $tasksOnBoard)
            ->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

    protected function close(): int
    {
        $this->teamService->explorer->beginTransaction();
        $sum = (int)$this->team->getNonRevokedSubmits()->sum('points');
        $this->teamService->storeModel([
            'points' => $sum,
        ], $this->team);
        $this->teamService->explorer->commit();
        return $sum;
    }
}
