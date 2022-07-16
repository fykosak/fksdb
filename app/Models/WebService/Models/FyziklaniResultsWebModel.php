<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class FyziklaniResultsWebModel extends WebModel
{

    private ServiceEvent $serviceEvent;
    private SubmitService $submitService;
    private TeamService2 $teamService;
    private TaskService $taskService;

    public function injectServices(
        ServiceEvent $serviceEvent,
        SubmitService $submitService,
        TeamService2 $teamService,
        TaskService $taskService
    ): void {
        $this->serviceEvent = $serviceEvent;
        $this->submitService = $submitService;
        $this->teamService = $teamService;
        $this->taskService = $taskService;
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->serviceEvent->findByPrimary($params['event_id']);
        $gameSetup = $event->getFyziklaniGameSetup();

        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'categories' => ['A', 'B', 'C'],
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
            'teams' => $this->teamService->serialiseTeams($event),
            'tasks' => $this->taskService->serialiseTasks($event),
            'times' => [
                'toStart' => $gameSetup->game_start->getTimestamp() - time(),
                'toEnd' => $gameSetup->game_end->getTimestamp() - time(),
                'visible' => $gameSetup->isResultsVisible(),
                'gameStart' => $gameSetup->game_start->format('c'),
                'gameEnd' => $gameSetup->game_end->format('c'),
            ],
        ];

        if ($gameSetup->isResultsVisible()) {
            $result['submits'] = $this->submitService->serialiseSubmits($event, null);
        }
        return $result;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int')->required(),
        ]);
    }
}
