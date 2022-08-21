<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\InvalidArgumentException;

class SameYearEvent implements EventRelation
{
    private int $eventTypeId;

    private EventService $eventService;

    public function __construct(int $eventTypeId, EventService $eventService)
    {
        $this->eventTypeId = $eventTypeId;
        $this->eventService = $eventService;
    }

    public function getEvent(EventModel $event): EventModel
    {
        $result = $this->eventService->getTable()->where([
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
