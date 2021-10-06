<?php

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\DI\Container;
use Nette\SmartObject;

class YearCalculator {

    use SmartObject;

    /**
     * @const No. of years of shift for forward registration.
     */
    private const FORWARD_SHIFT = 1;

    /**
     * @const First month of the academic year (for high schoolers).
     */
    public const FIRST_AC_MONTH = 9;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * The academic year starts at 1st day of self::FIRST_AC_MONTH.
     */
    public static function getCurrentAcademicYear(): int {
        $calYear = date('Y');
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $calYear -= 1;
        }
        return $calYear;
    }

    public static function getGraduationYear(int $studyYear, ?ModelContestYear $contestYear): int {
        $acYear = is_null($contestYear) ? self::getCurrentAcademicYear() : $contestYear->ac_year;

        if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        }
        throw new \InvalidArgumentException('Graduation year not match');
    }

    /**
     * @see getCurrentAcademicYear
     */
    public function getForwardShift(ModelContest $contest): int {
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $forwardYear = $contest->getCurrentContestYear()->year + self::FORWARD_SHIFT;
            $row = $contest->getContestYears()->where('year', $forwardYear)->fetch();

            /* Apply the forward shift only when the appropriate year is defined in the database */
            if ($this->container->getParameters()[$contest->getContestSymbol()]['forwardRegistration'] && (bool)$row) {
                return self::FORWARD_SHIFT;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}
