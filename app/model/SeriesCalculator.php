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

    public function getCurrentSeries($contest_id) {
        //TODO consider using tasks.submit_deadline
        return $this->getLastSeries($contest_id, $this->yearCalculator->getCurrentYear($contest_id));
    }

    public function getLastSeries($contest_id, $year) {
        $row = $this->serviceTask->getTable()->where(array(
                    'contest_id' => $contest_id,
                    'year' => $year
                ))->max('series');
        return $row;
    }

}
