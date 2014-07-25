<?php

use FKS\Config\GlobalParameters;
use Nette\Database\Table\ActiveRow;
use Nette\Object;

class YearCalculator extends Object {

    /**
     * @var ServiceContestYear
     */
    private $serviceContestYear;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;
    private $cache = null;
    private $revCache = null;

    function __construct(ServiceContestYear $serviceContestYear, GlobalParameters $globalParameters) {
        $this->serviceContestYear = $serviceContestYear;
        $this->globalParameters = $globalParameters;
        $this->preloadCache();
    }

    public function getAcademicYear(ActiveRow $contest, $year) {
        if (!isset($this->cache[$contest->contest_id]) || !isset($this->cache[$contest->contest_id][$year])) {
            throw new InvalidArgumentException("No academic year defined for {$contest->contest_id}:$year.");
        }
        return $this->cache[$contest->contest_id][$year];
    }

    /**
     * The academic year starts at September 1. (for high schoolers)
     * @return int
     */
    public function getCurrentAcademicYear() {
        $calYear = date('Y');
        $calMonth = date('m');
        if ($calMonth < 9) {
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
        if ($calMonth < 9) {
            $contestName = $this->globalParameters['contestMapping'][$contest->contest_id];
            return $this->globalParameters[$contestName]['forwardRegistration'] ? 1 : 0;
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
    }

}
