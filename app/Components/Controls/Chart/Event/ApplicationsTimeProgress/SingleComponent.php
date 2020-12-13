<?php

namespace FKSDB\Components\Controls\Chart\Event\ApplicationsTimeProgress;

use FKSDB\Components\Controls\Chart\Contestants\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelEventParticipant;
use FKSDB\Model\ORM\Models\ModelEventType;
use FKSDB\Model\ORM\Services\ServiceEvent;
use FKSDB\Model\ORM\Services\ServiceEventParticipant;
use Nette\DI\Container;

/**
 * Class SingleApplicationsTimeProgress
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleComponent extends ReactComponent implements Chart {

    private ServiceEventParticipant $serviceEventParticipant;
    private ModelEventType $eventType;
    private ServiceEvent $serviceEvent;

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, 'chart.events.participants.time-progress');
        $this->eventType = $event->getEventType();
    }

    final public function injectPrimary(ServiceEventParticipant $serviceEventParticipant, ServiceEvent $serviceEvent): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
        $this->serviceEvent = $serviceEvent;
    }

    protected function getData(): array {
        $data = [
            'participants' => [],
            'events' => [],
        ];
        /** @var ModelEvent $event */
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
        return $data;
    }

    public function getTitle(): string {
        return _('Applications time progress');
    }

    public function getControl(): self {
        return $this;
    }

    public function getDescription(): ?string {
        return null;
    }
}
