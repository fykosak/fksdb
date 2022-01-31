<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class FyziklaniResultsWebModel extends WebModel
{

    private ServiceEvent $serviceEvent;
    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;
    private ServiceFyziklaniTeam $serviceFyziklaniTeam;
    private ServiceFyziklaniTask $serviceFyziklaniTask;

    public function injectServices(
        ServiceEvent $serviceEvent,
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTask $serviceFyziklaniTask
    ): void {
        $this->serviceEvent = $serviceEvent;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
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
            'teams' => $this->serviceFyziklaniTeam->serialiseTeams($event),
            'tasks' => $this->serviceFyziklaniTask->serialiseTasks($event),
            'times' => [
                'toStart' => $gameSetup->game_start->getTimestamp() - time(),
                'toEnd' => $gameSetup->game_end->getTimestamp() - time(),
                'visible' => $gameSetup->isResultsVisible(),
                'gameStart' => $gameSetup->game_start->format('c'),
                'gameEnd' => $gameSetup->game_end->format('c'),
            ],
        ];

        if ($gameSetup->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->serialiseSubmits($event, null);
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
