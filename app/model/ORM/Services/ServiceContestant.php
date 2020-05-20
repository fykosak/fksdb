<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestant extends AbstractServiceSingle {
    /** @var string */
    protected $viewName = DbNames::VIEW_CONTESTANT;

    public function getModelClassName(): string {
        return ModelContestant::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_CONTESTANT_BASE;
    }

    /**
     * @note Read-only (loads data from view).
     *
     * @param ModelContest $contest
     * @param int $year
     * @return Selection
     */
    public function getCurrentContestants(ModelContest $contest, int $year): Selection {
        $contestants = $this->getContext()->table($this->viewName)
            ->select('*');

        $contestants->where([
            'v_contestant.contest_id' => $contest->contest_id,
            'v_contestant.year' => $year,
        ]);

        return $contestants;
    }
}
