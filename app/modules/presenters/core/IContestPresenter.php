<?php

/**
 * For presenters that provide contest and year context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */

use FKSDB\ORM\Models\ModelContest;

/**
 * Interface IContestPresenter
 */
interface IContestPresenter {
    /**
     * @return ModelContest
     * TODO noreturn type for tests
     */
    public function getSelectedContest();

    public function getSelectedYear(): ?int;

    public function getSelectedAcademicYear(): int;

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public function flashMessage($message, $type = 'info');
}
