<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelSubmit;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {
    /**
     * @var array
     */
    private $submitCache = [];

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelSubmit::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SUBMIT;
    }

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
