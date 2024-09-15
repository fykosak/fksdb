<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\WebService\Models\Events\EventWebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Expect;

/**
 * @phpstan-extends EventWebModel<array{eventId:int,lastUpdate?:string|null},array<string,mixed>>
 */
class ResultsWebModel extends EventWebModel
{
    protected SubmitService $submitService;

    public function injectServices(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws NotSetGameParametersException
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $event = $this->getEvent();
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

    protected function getExpectedParams(): array
    {
        return array_merge(
            parent::getExpectedParams(),
            [
            'lastUpdate' => Expect::string()->nullable(),
            ]
        );
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource(RestApiPresenter::RESOURCE_ID, $event),
            self::class,
            $event
        );
    }
}
