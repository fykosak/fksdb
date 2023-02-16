<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Nette\DI\Container;

class Handler
{
    private TeamService2 $teamService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    public function close(TeamModel2 $team): int
    {
        $this->teamService->explorer->beginTransaction();
        $sum = (int)$team->getNonRevokedSubmits()->sum('points');
        $this->teamService->storeModel([
            'points' => $sum,
        ], $team);
        $this->teamService->explorer->commit();
        return $sum;
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function getNextTask(TeamModel2 $team): ?TaskModel
    {
        $submits = $team->getNonRevokedSubmits()->count('*');
        $tasksOnBoard = $team->event->getGameSetup()->tasks_on_board;
        /** @var TaskModel|null $nextTask */
        $nextTask = $team->event
            ->getTasks()
            ->order('label')
            ->limit(1, $submits + $tasksOnBoard)
            ->fetch();
        return $nextTask;
    }
}
