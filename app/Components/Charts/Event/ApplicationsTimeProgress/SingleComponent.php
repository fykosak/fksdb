<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ApplicationsTimeProgress;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventTypeModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class SingleComponent extends FrontEndComponent implements Chart
{

    private EventTypeModel $eventType;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.participants.time-progress');
        $this->eventType = $event->event_type;
    }

    protected function getData(): array
    {
        $data = [
            'participants' => [],
            'events' => [],
        ];
        /** @var EventModel $event */
        foreach ($this->eventType->getEvents() as $event) {
            $participants = [];
            $query = $event->getPossiblyAttendingParticipants();
            /** @var EventParticipantModel $participant */
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

    public function getTitle(): string
    {
        return _('Applications time progress');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
