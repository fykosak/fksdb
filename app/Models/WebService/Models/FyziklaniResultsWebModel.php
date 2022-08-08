<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class FyziklaniResultsWebModel extends WebModel
{

    private EventService $eventService;
    private SubmitService $submitService;

    public function injectServices(
        EventService $eventService,
        SubmitService $submitService
    ): void {
        $this->eventService = $eventService;
        $this->submitService = $submitService;
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['event_id']);
        $gameSetup = $event->getFyziklaniGameSetup();

        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'categories' => array_map(
                fn(TeamCategory $category): string => $category->value,
                TeamCategory::casesForEvent($event)
            ),
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
            'teams' => TeamService2::serialiseTeams($event),
            'tasks' => TaskService::serialiseTasks($event),
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
