<?php

namespace FKSDB\Model;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Model\ORM\Models\ModelContest;
use FKSDB\Model\ORM\Models\ModelContestant;
use FKSDB\Model\ORM\Models\ModelContestYear;
use FKSDB\Model\ORM\Models\ModelLogin;
use FKSDB\Model\ORM\Services\ServiceContest;
use FKSDB\Model\ORM\Services\ServiceContestYear;
use InvalidArgumentException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;

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
    private array $cache = [];
    private array $revCache = [];
    private ?int $acYear;
    private Container $container;

    public function __construct(ServiceContestYear $serviceContestYear, ServiceContest $serviceContest, Container $container) {
        $this->serviceContestYear = $serviceContestYear;
        $this->serviceContest = $serviceContest;
        $this->container = $container;
        $this->acYear = $container->getParameters()['tester']['acYear'] ?? null;
        $this->preloadCache();
    }

    /**
     * @param ModelContest $contest
     * @param int|null $year
     * @return int
     * @throws InvalidArgumentException
     */
    public function getAcademicYear(ModelContest $contest, ?int $year): int {
        if (!isset($this->cache[$contest->contest_id]) || !isset($this->cache[$contest->contest_id][$year])) {
            throw new InvalidArgumentException("No academic year defined for {$contest->contest_id}:$year.");
        }
        return $this->cache[$contest->contest_id][$year];
    }

    /**
     * The academic year starts at 1st day of self::FIRST_AC_MONTH.
     * @return int
     */
    public function getCurrentAcademicYear(): int {
        if ($this->acYear !== null) {
            return $this->acYear;
        }
        $calYear = date('Y');
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $calYear -= 1;
        }
        return $calYear;
    }

    public function getGraduationYear(int $studyYear, ?int $acYear): int {
        $acYear = is_null($acYear) ? $this->getCurrentAcademicYear() : $acYear;

        if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        }
        throw new InvalidArgumentException('Graduation year not match');
    }

    public function getCurrentYear(ModelContest $contest): int {
        return $this->revCache[$contest->contest_id][$this->getCurrentAcademicYear()];
    }

    public function getFirstYear(ModelContest $contest): int {
        $years = array_keys($this->cache[$contest->contest_id]);
        return reset($years);
    }

    public function getLastYear(ModelContest $contest): int {
        $years = array_keys($this->cache[$contest->contest_id]);
        return end($years);
    }

    public function isValidYear(ModelContest $contest, ?int $year): bool {
        return !is_null($year) && $year >= $this->getFirstYear($contest) && $year <= $this->getLastYear($contest);
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
            $forwardYear = $this->getCurrentYear($contest) + self::FORWARD_SHIFT;
            $hasForwardYear = isset($this->cache[$contest->contest_id]) && isset($this->cache[$contest->contest_id][$forwardYear]);

            /* Apply the forward shift only when the appropriate year is defined in the database */
            if ($this->container->getParameters()[$contestName]['forwardRegistration'] && $hasForwardYear) {
                return self::FORWARD_SHIFT;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function preloadCache(): void {
        /** @var ModelContestYear $model */
        foreach ($this->serviceContestYear->getTable()->order('year') as $model) {
            if (!isset($this->cache[$model->contest_id])) {
                $this->cache[$model->contest_id] = [];
                $this->revCache[$model->contest_id] = [];
            }
            $this->cache[$model->contest_id][$model->year] = $model->ac_year;
            $this->revCache[$model->contest_id][$model->ac_year] = $model->year;
        }

        if (!$this->cache) {
            throw new InvalidStateException('FKSDB\YearCalculator cannot be initialized, table contest_year is probably empty.');
        }

        $pk = $this->serviceContest->getPrimary();
        $contests = $this->serviceContest->fetchPairs($pk, $pk);
        foreach ($contests as $contestId) {
            if (!array_key_exists($contestId, $this->revCache)) {
                throw new InvalidStateException(sprintf('Table contest_year does not specify any years at all for contest %s.', $contestId));
            }
            if (!array_key_exists($this->getCurrentAcademicYear(), $this->revCache[$contestId])) {
                throw new InvalidStateException(sprintf('Table contest_year does not specify year for contest %s for current academic year %s', $contestId, $this->getCurrentAcademicYear()));
            }
        }
    }

    public function getAvailableYears(string $role, ModelContest $contest, User $user): array {
        switch ($role) {
            case YearChooser::ROLE_ORG:
            case YearChooser::ROLE_ALL:
            case YearChooser::ROLE_SELECTED:
                $min = $this->getFirstYear($contest);
                $max = $this->getLastYear($contest);
                return array_reverse(range($min, $max));
            case YearChooser::ROLE_CONTESTANT:
                /** @var ModelLogin $login */
                $login = $user->getIdentity();
                $currentYear = $this->getCurrentYear($contest);
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
