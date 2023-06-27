<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\NetteORM\Service;

/**
 * @method EventModel storeModel(array $data, ?EventModel $model = null)
 * @method EventModel|null findByPrimary($key)
 */
class EventService extends Service
{

    public function getEvents(ContestYearModel $contestYear): TypedSelection
    {
        // TODO to related
        return $this->getTable()
            ->where('event_type.contest_id', $contestYear->contest_id)
            ->where('year', $contestYear->year);
    }

    public function getByEventTypeId(ContestYearModel $contestYear, int $eventTypeId): ?EventModel
    {
        return $this->getEvents($contestYear)->where('event_type_id', $eventTypeId)->fetch();
    }
}
