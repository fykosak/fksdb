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
 * Class SingleApplicationsTimeProgress
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleApplicationsTimeProgress extends ReactComponent implements IChart {

    private ServiceEventParticipant $serviceEventParticipant;

    private ModelEventType $eventType;

    private ServiceEvent $serviceEvent;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param ModelEvent $event
     */
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context);
        $this->eventType = $event->getEventType();
    }

    public function injectPrimary(ServiceEventParticipant $serviceEventParticipant, ServiceEvent $serviceEvent): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
        $this->serviceEvent = $serviceEvent;
    }

    protected function getReactId(): string {
        return 'events.applications-time-progress.participants';
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getData(): string {
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
            /** @var ModelEventParticipant $participant */
            foreach ($query as $participant) {
                $participants[] = [
                    'created' => $participant->created->format('c'),
                ];
            }

            $data['participants'][$event->event_id] = $participants;
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return Json::encode($data);
    }

    public function getAction(): string {
        return 'singleApplicationProgress';
    }

    public function getTitle(): string {
        return 'Applications time progress';
    }

    public function getControl(): Control {
        return $this;
    }

    public function getDescription(): ?string {
        return null;
    }
}
