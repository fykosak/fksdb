<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEvent extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT;
    protected $modelClassName = 'ModelEvent';

    public function getEvents() {
        $schools = $this->getTable()
                ->select(DbNames::TAB_EVENT . '.*')
                ->select(DbNames::TAB_EVENT_TYPE . '.*');
        return $schools;
    }

}

