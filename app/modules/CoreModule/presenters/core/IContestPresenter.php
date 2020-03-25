<?php

namespace FKSDB\CoreModule;

use FKSDB\ORM\Models\ModelContest;

/**
 * For presenters that provide contest and year context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * Interface IContestPresenter
 */
interface IContestPresenter {

    /** @return ModelContest */
    public function getSelectedContest();

    /** @return int */
    public function getSelectedYear(): int;

    /** @return int */
    public function getSelectedAcademicYear(): int;
}

