<?php

namespace FKSDB\Models;

use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\ORM\Services\ServiceContestYear;
use InvalidArgumentException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;

/**
 * Class FKSDB\YearCalculator
 */
class YearCalculator {

    /**
     * @const No. of years of shift for forward registration.
     */
    private const FORWARD_SHIFT = 1;

    /**
     * @const First month of the academic year (for high schoolers).
     */
    public const FIRST_AC_MONTH = 9;

    private ServiceContestYear $serviceContestYear;

    private ServiceContest $serviceContest;

    private Container $container;

    public function __construct(ServiceContestYear $serviceContestYear, ServiceContest $serviceContest, Container $container) {
        $this->serviceContestYear = $serviceContestYear;
        $this->serviceContest = $serviceContest;
        $this->container = $container;
    }

    /**
     * The academic year starts at 1st day of self::FIRST_AC_MONTH.
     * @return int
     */
    public static function getCurrentAcademicYear(): int {
        $calYear = date('Y');
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $calYear -= 1;
        }
        return $calYear;
    }

    public function getGraduationYear(int $studyYear, ?int $acYear): int {
        $acYear = is_null($acYear) ? self::getCurrentAcademicYear() : $acYear;

        if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        }
        throw new InvalidArgumentException('Graduation year not match');
    }

    /**
     * @param ModelContest $contest
     * @return int
     * @see getCurrentAcademicYear
     */
    public function getForwardShift(ModelContest $contest): int {
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $contestName = $this->container->getParameters()['contestMapping'][$contest->contest_id];
            $forwardYear = $contest->getCurrentYear() + self::FORWARD_SHIFT;
            $row = $contest->getContestYears()->where('year', $forwardYear)->fetch();

            /* Apply the forward shift only when the appropriate year is defined in the database */
            if ($this->container->getParameters()[$contestName]['forwardRegistration'] && (bool)$row) {
                return self::FORWARD_SHIFT;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getAvailableYears(string $role, ModelContest $contest, User $user): array {
        switch ($role) {
            case YearChooserComponent::ROLE_ORG:
            case YearChooserComponent::ROLE_ALL:
            case YearChooserComponent::ROLE_SELECTED:
                return array_reverse(range($contest->getFirstYear(), $contest->getLastYear()));
            case YearChooserComponent::ROLE_CONTESTANT:
                /** @var ModelLogin $login */
                $login = $user->getIdentity();
                $currentYear = $contest->getCurrentYear();
                $years = [];
                if ($login && !$login->getPerson()) {
                    $contestants = $login->getPerson()->getContestants($contest);
                    /** @var ModelContestant $contestant */
                    foreach ($contestants as $contestant) {
                        $years[] = $contestant->year;
                    }
                }
                sort($years);
                return count($years) ? $years : [$currentYear];
            default:
                throw new InvalidStateException(sprintf('Role %s is not supported', $role));
        }
    }
}
