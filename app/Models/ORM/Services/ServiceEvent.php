<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\NetteORM\Service;

/**
 * @method ModelEvent createNewModel(array $data)
 * @method ModelEvent|null findByPrimary($key)
 */
class ServiceEvent extends Service
{

    public function getEvents(ModelContestYear $contestYear): TypedSelection
    {
        // TODO to related
        return $this->getTable()
            ->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contestYear->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $contestYear->year);
    }

    public function getByEventTypeId(ModelContestYear $contestYear, int $eventTypeId): ?ModelEvent
    {
        /** @var ModelEvent $event */
        $event = $this->getEvents($contestYear)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event;
    }
}
