<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SUBMIT;
    protected $modelClassName = 'ModelSubmit';
    private $cache = array();

    /**
     * Syntactic sugar.
     * 
     * @param int $key
     * @return ModelSubmit|null
     */
    public function findByContestant($ctId, $taskId) {
        $key = $ctId . ':' . $taskId;

        
        if (!array_key_exists($key, $this->cache)) {
            $result = $this->getTable()->where(array(
                        'ct_id' => $ctId,
                        'task_id' => $taskId,
                    ))->fetch();

            if ($result !== false) {
                $this->cache[$key] = $result;
            } else {
                $this->cache[$key] = null;
            }
        }
        return $this->cache[$key];
    }

    public function getSubmits() {
        $submits = $this->getTable()
                ->select(DbNames::TAB_SUBMIT . '.*')
                ->select(DbNames::TAB_TASK . '.*');
        return $submits;
    }

}

