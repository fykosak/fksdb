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
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @param int $series
     * @param int $tasknr
     * @return \FKSDB\ORM\Models\ModelTask|null
     */
    public function findBySeries(ModelContest $contest, $year, $series, $tasknr, $is_quiz) {
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
            'is_quiz' => $is_quiz,
        ])->fetch();

        if ($result !== false) {
            return ModelTask::createFromActiveRow($result);
        } else {
            return null;
        }
    }

}
