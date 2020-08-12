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

    public function getSelectedContest(): ?ModelContest;

    public function getSelectedYear(): ?int;

    public function getSelectedAcademicYear(): int;

    /**
     * @param string $message
     * @param string $type
     * @return \stdClass
     */
    public function flashMessage($message, string $type = 'info'): \stdClass;
}
