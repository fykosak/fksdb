<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SCHOOL;
    protected $modelClassName = 'ModelSchool';

    public function getSchools() {
        $schools = $this->getTable()
            ->select(DbNames::TAB_SCHOOL . '.*')
            ->select(DbNames::TAB_ADDRESS . '.*');
        return $schools;
    }

}

