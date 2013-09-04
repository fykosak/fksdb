<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SUBMIT;
    protected $modelClassName = 'ModelSubmit';

    /**
     * Syntactic sugar.
     * 
     * @param int $key
     * @return ModelSubmit|null
     */
    public function findByContestant($ctId, $taskId) {
        $result = $this->getTable()->where(array(
                    'ct_id' => $ctId,
                    'task_id' => $taskId,
                ))->fetch();
        
        if ($result !== false) {
            return $result;
        } else {
            return null;
        }
    }

}

