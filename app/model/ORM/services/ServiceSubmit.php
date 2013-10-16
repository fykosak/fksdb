<?php

use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SUBMIT;
    protected $modelClassName = 'ModelSubmit';
    private $submit_cache = array();

    /**
     * Syntactic sugar.
     * 
     * @param int $ctId
     * @param int $taskId
     * @return ModelSubmit|null
     */
    public function findByContestant($ctId, $taskId) {
        $key = $ctId . ':' . $taskId;

        if (!array_key_exists($key, $this->submit_cache)) {
            $result = $this->getTable()->where(array(
                        'ct_id' => $ctId,
                        'task_id' => $taskId,
                    ))->fetch();

            if ($result !== false) {
                $this->submit_cache[$key] = $result;
            } else {
                $this->submit_cache[$key] = null;
            }
        }
        return $this->submit_cache[$key];
    }

    /**
     * 
     * @return Selection
     */
    public function getSubmits() {
        $submits = $this->getTable()
                ->select(DbNames::TAB_SUBMIT . '.*')
                ->select(DbNames::TAB_TASK . '.*');
        return $submits;
    }

}

