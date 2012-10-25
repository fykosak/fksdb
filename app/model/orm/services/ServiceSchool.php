<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SCHOOL;
    protected $modelClassName = 'ModelSchool';
    
    public function getSchools(){
        $schools = $this->getTable()            
                ->select('school.*')
                ->select('address.*');
        return $schools;
    }

}

