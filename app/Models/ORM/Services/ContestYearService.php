<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Service;

class ContestYearService extends Service
{
    /**
     * @const First month of the academic year (for high schoolers).
     */
    public const FIRST_AC_MONTH = 9;

    /**
     * The academic year starts at 1st day of self::FIRST_AC_MONTH.
     */
    public static function getCurrentAcademicYear(): int
    {
        $calYear = date('Y');
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $calYear -= 1;
        }
        return (int)$calYear;
    }

    public function findByContestAndYear(int $contestId, int $year): ?ContestYearModel
    {
        return $this->getTable()->where('contest_id', $contestId)->where('year', $year)->fetch();
    }
}
