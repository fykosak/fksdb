<?php

use FKSDB\Config\GlobalParameters;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Object;
use Nette\Utils\Arrays;

class YearCalculator extends Object {

    /**
     * @const No. of years of shift for forward registration.
     */
    const FORWARD_SHIFT = 1;

    /**
     * @const First month of the academic year (for high schoolers).
     */
    const FIRST_AC_MONTH = 9;

    /**
     * @var ServiceContestYear
     */
    private $serviceContestYear;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;
    private $cache = null;
    private $revCache = null;
    private $acYear;

    function __construct(ServiceContestYear $serviceContestYear, ServiceContest $serviceContest, GlobalParameters $globalParameters) {
        $this->serviceContestYear = $serviceContestYear;
        $this->serviceContest = $serviceContest;
        $this->globalParameters = $globalParameters;
        $this->acYear = Arrays::get($this->globalParameters['tester'], 'acYear', null);
        $this->preloadCache();
    }

    public function getAcademicYear(ActiveRow $contest, $year) {
        if (!isset($this->cache[$contest->contest_id]) || !isset($this->cache[$contest->contest_id][$year])) {
            throw new InvalidArgumentException("No academic year defined for {$contest->contest_id}:$year.");
        }
        return $this->cache[$contest->contest_id][$year];
    }

    /**
     * The academic year starts at 1st day of self::FIRST_AC_MONTH.
     * @return int
     */
    public function getCurrentAcademicYear() {
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

    public function getGraduationYear($studyYear, $acYear = null) {
        $acYear = ($acYear !== null) ? $acYear : $this->getCurrentAcademicYear();

        if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        }
    }

    public function getCurrentYear(ModelContest $contest) {
        return $this->revCache[$contest->contest_id][$this->getCurrentAcademicYear()];
    }

    public function getFirstYear(ModelContest $contest) {
        $years = array_keys($this->cache[$contest->contest_id]);
        return $years[0];
    }

    public function getLastYear(ModelContest $contest) {
        $years = array_keys($this->cache[$contest->contest_id]);
        return $years[count($years) - 1];
    }

    public function isValidYear(ModelContest $contest, $year) {
        return $year !== null && $year >= $this->getFirstYear($contest) && $year <= $this->getLastYear($contest);
    }

    /**
     * @see getCurrentAcademicYear
     * @param ModelContest $contest
     * @return int
     */
    public function getForwardShift(ModelContest $contest) {
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

    private function preloadCache() {
        $this->cache = array();
        $this->revCache = array();
        foreach ($this->serviceContestYear->getTable()->order('year') as $row) {
            if (!isset($this->cache[$row->contest_id])) {
                $this->cache[$row->contest_id] = array();
                $this->revCache[$row->contest_id] = array();
            }
            $this->cache[$row->contest_id][$row->year] = $row->ac_year;
            $this->revCache[$row->contest_id][$row->ac_year] = $row->year;
        }

        if (!$this->cache) {
            throw new InvalidStateException('YearCalculator cannot be initalized, table contest_year is probably empty.');
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
