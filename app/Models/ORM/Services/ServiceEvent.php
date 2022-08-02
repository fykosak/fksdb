<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\NetteORM\Service;

/**
 * @method EventModel createNewModel(array $data)
 * @method EventModel|null findByPrimary($key)
 */
class ServiceEvent extends Service
{

    public function getEvents(ContestYearModel $contestYear): TypedSelection
    {
        // TODO to related
        return $this->getTable()
            ->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contestYear->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $contestYear->year);
    }

    public function getByEventTypeId(ContestYearModel $contestYear, int $eventTypeId): ?EventModel
    {
        /** @var EventModel $event */
        $event = $this->getEvents($contestYear)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event;
    }
}
