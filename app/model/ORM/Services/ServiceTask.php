<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\BadRequestException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTask extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelTask::class;
    }

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
        /** @var ModelTask $result */
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
        ])->fetch();
        return $result ?: null;
    }

    public function getAvailableTasks(ModelContest $contest, int $year): TypedTableSelection {
        return $this->getTable()
            ->where('contest_id = ? AND year = ?', $contest->contest_id, $year)
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()')
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }
}
