<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;
use Nette\Utils\Json;

/**
 * Class TeamApplicationsTimeProgress
 * @package FKSDB\Components\React\ReactComponent\Events
 */
class TeamApplicationsTimeProgress extends ReactComponent {
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var \FKSDB\ORM\Models\ModelEvent[]
     */
    private $events;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param array $events
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(Container $context, array $events, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam) {
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
         * @var \FKSDB\ORM\Models\ModelEvent $event
         */
        foreach ($this->events as $event) {
            $data['teams'][$event->event_id] = $this->serviceFyziklaniTeam->getTeamsAsArray($event);
            $data['events'][$event->event_id]=$event->__toArray();
        }
        return Json::encode($data);
    }
}
