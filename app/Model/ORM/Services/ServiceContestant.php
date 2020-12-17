<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelContest;
use FKSDB\Model\ORM\Models\ModelContestant;
use FKSDB\ORM\DeprecatedLazyService;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestant extends AbstractServiceSingle {
    use DeprecatedLazyService;

    protected string $viewName = DbNames::VIEW_CONTESTANT;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_CONTESTANT_BASE, ModelContestant::class);
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
