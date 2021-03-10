<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestant extends AbstractServiceSingle {

    protected string $viewName = DbNames::VIEW_CONTESTANT;

    /**
     * @note Read-only (loads data from view).
     *
     * @param ModelContest $contest
     * @param int $year
     * @return Selection
     * TODO
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
