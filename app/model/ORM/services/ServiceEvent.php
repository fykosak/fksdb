<?php

namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEvent extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEvent';

    /**
     * @param ModelContest $contest
     * @param $year
     * @return Selection
     */
    public function getEvents(ModelContest $contest, int $year): Selection {
        $result = $this->getTable()
            ->select(DbNames::TAB_EVENT . '.*')
            ->select(DbNames::TAB_EVENT_TYPE . '.name AS `type_name`');
        $result->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contest->contest_id)
            ->where(DbNames::TAB_EVENT . '.year', $year);
        return $result;
    }

    /**
     * @param ModelContest $contest
     * @param int $year
     * @param int $eventTypeId
     * @return ActiveRow
     */
    public function getByEventTypeId(ModelContest $contest, int $year, int $eventTypeId): ActiveRow {
        return $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
    }

}
