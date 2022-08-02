<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\InvalidArgumentException;

class SameYearEvent implements EventRelation
{
    private int $eventTypeId;

    private ServiceEvent $serviceEvent;

    public function __construct(int $eventTypeId, ServiceEvent $serviceEvent)
    {
        $this->eventTypeId = $eventTypeId;
        $this->serviceEvent = $serviceEvent;
    }

    public function getEvent(EventModel $event): EventModel
    {
        $result = $this->serviceEvent->getTable()->where([
            'event_type_id' => $this->eventTypeId,
            'year' => $event->year,
        ]);
        /** @var EventModel|null $event */
        $event = $result->fetch();
        if ($event === null) {
            throw new InvalidArgumentException(
                'No event with event_type_id ' . $this->eventTypeId . ' for the year ' . $event->year . '.'
            );
        } elseif ($result->fetch() !== null) {
            throw new InvalidArgumentException(
                'Ambiguous events with event_type_id ' . $this->eventTypeId . ' for the year ' . $event->year . '.'
            );
        } else {
            return $event;
        }
    }
}
