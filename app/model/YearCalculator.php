<?php

namespace FKSDB;

use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestYear;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\ORM\Services\ServiceContestYear;
use InvalidArgumentException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;

/**
 * Class FKSDB\YearCalculator
 */
class YearCalculator {

    /**
     * @const No. of years of shift for forward registration.
     */
    public const FORWARD_SHIFT = 1;

    /**
     * @const First month of the academic year (for high schoolers).
     */
    public const FIRST_AC_MONTH = 9;

    private ServiceContestYear $serviceContestYear;

    private ServiceContest $serviceContest;

    private GlobalParameters $globalParameters;
    /**
     * @var int[][]
     */
    private $cache = [];
    /**
     * @var int[][]
     */
    private $revCache = [];
    /**
     * @var int
     */
    private $acYear;

    /**
     * FKSDB\YearCalculator constructor.
     * @param ServiceContestYear $serviceContestYear
     * @param ServiceContest $serviceContest
     * @param GlobalParameters $globalParameters
     */
    public function __construct(ServiceContestYear $serviceContestYear, ServiceContest $serviceContest, GlobalParameters $globalParameters) {
        $this->serviceContestYear = $serviceContestYear;
        $this->serviceContest = $serviceContest;
        $this->globalParameters = $globalParameters;
        $this->acYear = Arrays::get($this->globalParameters['tester'], 'acYear', null);
        $this->preloadCache();
    }

    /**
     * @param ActiveRow|ModelContest $contest
     * @param $year
     * @return int
     * @throws InvalidArgumentException
     */
    public function getAcademicYear(ActiveRow $contest, int $year): int {
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

    /**
     * @param int $studyYear
     * @param int|null $acYear
     * @return int
     */
    public function getGraduationYear(int $studyYear, int $acYear = null): int {
        $acYear = ($acYear !== null) ? $acYear : $this->getCurrentAcademicYear();

        if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        }
        throw new \Nette\InvalidArgumentException('Graduation year not match');
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

    public function isValidYear(ModelContest $contest, int $year = null): bool {
        return $year !== null && $year >= $this->getFirstYear($contest) && $year <= $this->getLastYear($contest);
    }

    /**
     * @param ModelContest $contest
     * @return int
     * @see getCurrentAcademicYear
     */
    public function getForwardShift(ModelContest $contest): int {
        $calMonth = date('m');
        if ($calMonth < self::FIRST_AC_MONTH) {
            $contestName = $this->globalParameters['contestMapping'][$contest->contest_id];
            $forwardYear = $this->getCurrentYear($contest) + self::FORWARD_SHIFT;
            $hasForwardYear = isset($this->cache[$contest->contest_id]) && isset($this->cache[$contest->contest_id][$forwardYear]);

            /* Apply the forward shift only when the appropriate year is defined in the database */
            if ($this->globalParameters[$contestName]['forwardRegistration'] && $hasForwardYear) {
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
            throw new InvalidStateException('FKSDB\YearCalculator cannot be initalized, table contest_year is probably empty.');
        }

        $pk = $this->serviceContest->getPrimary();
        $contests = $this->serviceContest->fetchPairs($pk, $pk);
        foreach ($contests as $contestId) {
            if (!array_key_exists($contestId, $this->revCache)) {
                throw new InvalidStateException(sprintf('Table contest_year doesn\'t specify any years at all for contest %s.', $contestId));
            }
            if (!array_key_exists($this->getCurrentAcademicYear(), $this->revCache[$contestId])) {
                throw new InvalidStateException(sprintf('Table contest_year doesn\'t specify year for contest %s for current academic year %s', $contestId, $this->getCurrentAcademicYear()));
            }
        }
    }
}
