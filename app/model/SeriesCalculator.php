<?php

use Nette\Object;

class SeriesCalculator extends Object {

    /**
     * @var ServiceTask
     */

    private $serviceTask;

    /**
     *
     * @var YearCalculator
     */
    private $yearCalculator;

    public function __construct(ServiceTask $serviceTask, YearCalculator $yearCalculator) {
        $this->serviceTask = $serviceTask;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelContest $contest
     * @return int
     */
    public function getCurrentSeries(ModelContest $contest) {
        $lastSeries = $this->getLastSeries($contest, $this->yearCalculator->getCurrentYear($contest));
        return ($lastSeries == 1) ? 1 : $lastSeries - 1;
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     * @return int
     */
    public function getLastSeries(ModelContest $contest, $year) {
        $row = $this->serviceTask->getTable()->where(array(
                    'contest_id' => $contest->contest_id,
                    'year' => $year
                ))->max('series');
        return $row;
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     * @return int
     */
    public function getTotalSeries(ModelContest $contest, $year) {
        //TODO allow variance?
        return 6;
    }

}
