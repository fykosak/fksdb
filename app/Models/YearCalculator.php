<?php

declare(strict_types=1);

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\SmartObject;

class YearCalculator
{
    use SmartObject;

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

    public static function getGraduationYear(int $studyYear, ContestYearModel $contestYear): int
    {
        if ($studyYear >= 6 && $studyYear <= 9) {
            return $contestYear->ac_year + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $contestYear->ac_year + (5 - $studyYear);
        }
        throw new \InvalidArgumentException('Graduation year not match');
    }
}
