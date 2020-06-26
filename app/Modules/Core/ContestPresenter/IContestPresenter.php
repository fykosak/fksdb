<?php

/**
 * For presenters that provide contest and year context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */

namespace FKSDB\Modules\Core\ContestPresenter;

use FKSDB\ORM\Models\ModelContest;

/**
 * Interface IContestPresenter
 */
interface IContestPresenter {

    /** @return ModelContest */
    public function getSelectedContest();

    /** @return int */
    public function getSelectedYear();

    public function getSelectedAcademicYear(): int;

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public function flashMessage($message, $type = 'info');
}
