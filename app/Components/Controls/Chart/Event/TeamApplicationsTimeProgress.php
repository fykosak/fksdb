<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TeamApplicationsTimeProgress
 * @package FKSDB\Components\React\ReactComponent\Events
 */
class TeamApplicationsTimeProgress extends ReactComponent implements IChart {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ModelEventType
     */
    private $eventType;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param ModelEvent $event
     */
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context);
        $this->eventType = $event->getEventType();
        $this->serviceFyziklaniTeam = $context->getByType(ServiceFyziklaniTeam::class);
        $this->serviceEvent = $context->getByType(ServiceEvent::class);
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
        /**
         * @var ModelEvent $event
         */
        foreach ($this->serviceEvent->getEventsByType($this->eventType) as $event) {
            $data['teams'][$event->event_id] = $this->serviceFyziklaniTeam->getTeamsAsArray($event);
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return Json::encode($data);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string {
        return 'teamApplicationProgress';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return 'Team applications time progress';
    }

    /**
     * @inheritDoc
     */
    public function getControl(): Control {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return null;
    }
}
