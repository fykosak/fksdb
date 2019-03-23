<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SUBMIT;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelSubmit';
    private $submitCache = [];

    /**
     * Syntactic sugar.
     *
     * @param int $ctId
     * @param int $taskId
     * @return \FKSDB\ORM\Models\ModelSubmit|null
     */
    public function findByContestant($ctId, $taskId) {
        $key = $ctId . ':' . $taskId;

        if (!array_key_exists($key, $this->submitCache)) {
            $result = $this->getTable()->where([
                'ct_id' => $ctId,
                'task_id' => $taskId,
            ])->fetch();

            if ($result !== false) {
                $this->submitCache[$key] = $result;
            } else {
                $this->submitCache[$key] = null;
            }
        }
        return $this->submitCache[$key];
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
