<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method findByPrimary($key) : ?ModelEvent
 */
class ServiceEvent extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelEvent::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_EVENT;
    }

    public function getEvents(ModelContest $contest, int $year): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_EVENT . '.*')
            ->select(DbNames::TAB_EVENT_TYPE . '.name AS `type_name`')
            ->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contest->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $year);
    }

    /**
     * @param ModelContest $contest
     * @param int $year
     * @param int $eventTypeId
     * @return ModelEvent|null
     * TODO
     */
    public function getByEventTypeId(ModelContest $contest, int $year, int $eventTypeId): ?ModelEvent {
        /** @var ModelEvent $event */
        $event = $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event ?: null;
    }

    public function getEventsByType(ModelEventType $eventType): TypedTableSelection {
        return $this->getTable()->where('event_type_id', $eventType->event_type_id);
    }
}
