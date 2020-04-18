<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTask extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelTask::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_TASK;
    }

    /**
     * Syntactic sugar.
     *
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     * @param int $tasknr
     * @return ModelTask|null
     */
    public function findBySeries(ModelContest $contest, int $year, int $series, int $tasknr) {
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
        ])->fetch();

        if ($result !== false) {
            return ModelTask::createFromActiveRow($result);
        } else {
            return null;
        }
    }

}
