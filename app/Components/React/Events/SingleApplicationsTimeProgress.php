<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TeamApplicationsTimeProgress
 * @package FKSDB\Components\React\ReactComponent\Events
 */
class SingleApplicationsTimeProgress extends ReactComponent {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

    /**
     * @var ModelEvent[]
     */
    private $events;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param array $events
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function __construct(Container $context, array $events, ServiceEventParticipant $serviceEventParticipant) {
        parent::__construct($context);
        $this->events = $events;
        $this->serviceEventParticipant = $serviceEventParticipant;

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
        return 'participants';
    }

    /**
     * @return string
     * @throws JsonException
     */
    function getData(): string {
        $data = [
            'participants' => [],
            'events' => [],
        ];
        foreach ($this->events as $event) {
            $participants = [];
            $query = $this->serviceEventParticipant->findPossiblyAttending($event);
            foreach ($query as $row) {
                $participants[] = [
                    'created' => ModelEventParticipant::createFromActiveRow($row)->created->format('c'),
                ];
            }

            $data['participants'][$event->event_id] = $participants;
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return Json::encode($data);
    }
}
