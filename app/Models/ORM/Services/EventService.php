<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-extends Service<EventModel>
 */
final class EventService extends Service
{
    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    public function getEventsWithOpenRegistration(): TypedSelection
    {
        return $this->getTable()
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
    }

    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    public function getEvents(ContestYearModel $contestYear): TypedSelection
    {
        // TODO to related
        return $this->getTable()
            ->where('event_type.contest_id', $contestYear->contest_id)
            ->where('year', $contestYear->year);
    }

    public function getByEventTypeId(ContestYearModel $contestYear, int $eventTypeId): ?EventModel
    {
        /** @var EventModel|null $event */
        $event = $this->getEvents($contestYear)->where('event_type_id', $eventTypeId)->fetch();
        return $event;
    }
}
