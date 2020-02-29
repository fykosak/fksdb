<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TeamApplicationsTimeProgress
 * @package FKSDB\Components\React\ReactComponent\Events
 */
class TeamApplicationsTimeProgress extends ReactComponent {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ModelEvent[]
     */
    private $events;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param array $events
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(Container $context, array $events, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        parent::__construct($context);
        $this->events = $events;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;

    }

    /**
     * @return string
     */
    protected function getReactId(): string {
        return 'events.applications-time-progress.teams';
    }

    /**
     * @return string
     * @throws JsonException
     */
    function getData(): string {
        $data = [
            'teams' => [],
            'events' => [],
        ];
        foreach ($this->events as $event) {
            $data['teams'][$event->event_id] = $this->serviceFyziklaniTeam->getTeamsAsArray($event);
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return Json::encode($data);
    }
}
