<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use Nette\Utils\Json;
use ORM\Services\Events\ServiceFyziklaniTeam;

class TeamApplicationsTimeProgress extends ReactComponent {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ModelEvent[]
     */
    private $events;

    public function __construct(Container $context, array $events, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        parent::__construct($context);
        $this->events = $events;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;

    }

    /**
     * @return string
     */
    function getComponentName(): string {
        return 'applications-time-progress';
    }

    /**
     * @return string
     */
    function getModuleName(): string {
        return 'events';
    }

    /**
     * @return string
     */
    function getMode(): string {
        return 'team';
    }

    /**
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    function getData(): string {
        $data = [
            'teams'=>[],
            'events'=>[],
        ];
        /**
         * @var $event ModelEvent
         */
        foreach ($this->events as $event) {
            $data['teams'][$event->event_id] = $this->serviceFyziklaniTeam->getTeamsArray($event);
            $data['events'][$event->event_id]=$event->__toArray();
        }
        return Json::encode($data);
    }
}
