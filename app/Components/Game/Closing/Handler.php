<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class Handler
{
    private TeamService2 $teamService;
    public MemoryLogger $logger;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->logger = new MemoryLogger();
    }

    public function inject(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    public function close(TeamModel2 $team, bool $checkRequirements = true): void
    {
        if ($checkRequirements) {
            $team->canClose();
        }
        $this->teamService->explorer->beginTransaction();
        $sum = (int)$team->getNonRevokedSubmits()->sum('points');
        $this->teamService->storeModel([
            'points' => $sum,
        ], $team);
        $this->teamService->explorer->commit();
        $this->logger->log(
            new Message(
                \sprintf(
                    _('Team "%s" has successfully closed submitting, with total %d points.'),
                    $team->name,
                    $sum
                ),
                Message::LVL_SUCCESS
            )
        );
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
