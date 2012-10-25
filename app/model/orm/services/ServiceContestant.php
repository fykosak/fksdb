<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_CONTESTANT;
    protected $modelClassName = 'ModelContestant';

    public function getCurrentContestants($contest_id, $year) {
        $contestants = $this->getTable()
                ->select('person.display_name AS display_name')
                ->select('contestant.study_year')
                ->select('school.name AS school_name');

        $contestants->where(array(
            'contestant.contest_id' => $contest_id,
            'contestant.year' => $year,
        ));
        
        return $contestants;
    }

}

