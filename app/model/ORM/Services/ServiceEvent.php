<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEvent extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelEvent::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT;
    }

    /**
     * @param ModelContest $contest
     * @param $year
     * @return TypedTableSelection
     */
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
    public function getByEventTypeId(ModelContest $contest, int $year, int $eventTypeId) {
        /** @var ModelEvent $event */
        $event = $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
        return $event ?: null;
    }

    /**
     * @param ModelEventType $eventType
     * @return TypedTableSelection
     */
    public function getEventsByType(ModelEventType $eventType): TypedTableSelection {
        return $this->getTable()->where('event_type_id', $eventType->event_type_id);
    }
}
