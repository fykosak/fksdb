<?php

namespace FKSDB\Components\Charts\Event\ApplicationsTimeProgress;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelEventType;
use Nette\DI\Container;

class SingleComponent extends ReactComponent implements Chart {

    private ModelEventType $eventType;

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, 'chart.events.participants.time-progress');
        $this->eventType = $event->getEventType();
    }

    protected function getData(): array {
        $data = [
            'participants' => [],
            'events' => [],
        ];
        foreach ($this->eventType->getEventsByType() as $eventRow) {
            $event = ModelEvent::createFromActiveRow($eventRow);
            $participants = [];
            $query = $event->getPossiblyAttendingParticipants();
            /** @var ModelEventParticipant $participant */
            foreach ($query as $row) {
                $participant = ModelEventParticipant::createFromActiveRow($row);
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
