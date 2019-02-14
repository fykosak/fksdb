<?php

use FKSDB\ORM\ModelContest;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEvent extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT;
    protected $modelClassName = 'FKSDB\ORM\ModelEvent';

    /**
     * @param ModelContest $contest
     * @param $year
     * @return \Nette\Database\Table\Selection
     */
    public function getEvents(ModelContest $contest, $year) {
        $result = $this->getTable()
                ->select(DbNames::TAB_EVENT . '.*')
                ->select(DbNames::TAB_EVENT_TYPE . '.name AS `type_name`');
        $result->where(DbNames::TAB_EVENT_TYPE . '.contest_id', $contest->contest_id)
                ->where(DbNames::TAB_EVENT . '.year', $year);
        return $result;
    }

    /**
     * @param ModelContest $contest
     * @param $year
     * @param $eventTypeId
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getByEventTypeId(ModelContest $contest, $year, $eventTypeId) {
        return $this->getEvents($contest, $year)->where(DbNames::TAB_EVENT . '.event_type_id', $eventTypeId)->fetch();
    }

}
