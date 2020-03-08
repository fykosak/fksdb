<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TeamApplicationsTimeProgress
 * @package FKSDB\Components\React\ReactComponent\Events
 */
class SingleApplicationsTimeProgress extends ReactComponent implements IChart {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

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
        $this->serviceEventParticipant = $context->getByType(ServiceEventParticipant::class);
        $this->serviceEvent = $context->getByType(ServiceEvent::class);
    }

    /**
     * @return string
     */
    protected function getReactId(): string {
        return 'events.applications-time-progress.participants';
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
        /**
         * @var ModelEvent $event
         */
        foreach ($this->serviceEvent->getEventsByType($this->eventType) as $event) {
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

    /**
     * @inheritDoc
     */
    public function getAction(): string {
        return 'singleApplicationProgress';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return 'Applications time progress';
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
