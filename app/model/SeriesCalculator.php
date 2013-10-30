<?php

use Nette\Object;

class SeriesCalculator extends Object {

    const YEAR = 31557600; //365.25*24*3600

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
        // TODO consider using tasks.submit_deadline
        // TODO and define what current series actually is (it may differ depending on the contest)
        return $this->getLastSeries($contest, $this->yearCalculator->getCurrentYear($contest));
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
