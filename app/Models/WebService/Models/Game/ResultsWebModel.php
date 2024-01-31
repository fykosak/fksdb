<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{eventId:int,lastUpdate?:string|null},array<string,mixed>>
 */
class ResultsWebModel extends WebModel
{
    private EventService $eventService;
    private SubmitService $submitService;

    public function injectServices(EventService $eventService, SubmitService $submitService): void
    {
        $this->eventService = $eventService;
        $this->submitService = $submitService;
    }

    /**
     * @throws NotSetGameParametersException
     */
    protected function getJsonResponse(): array
    {
        $event = $this->eventService->findByPrimary($this->params['eventId']);
        $gameSetup = $event->getGameSetup();

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

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
            'lastUpdate' => Expect::string()->nullable(),
        ]);
    }

    protected function isAuthorized(): bool
    {
        return false;
    }
}
