<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\TypedTableSelection;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEvent createNewModel(array $data)
 * @method ModelEvent|null findByPrimary($key)
 * @method ModelEvent refresh(AbstractModel $model)
 */
class ServiceEvent extends AbstractService {

    public function getEvents(ModelContest $contest, int $year): TypedTableSelection {
        // TODO to related
        return $this->getTable()
            ->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contest->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $year);
    }

    public function getByEventTypeId(ModelContest $contest, int $year, int $eventTypeId): ?ModelEvent {
        /** @var ModelEvent $event */
        $event = $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event;
    }
}
