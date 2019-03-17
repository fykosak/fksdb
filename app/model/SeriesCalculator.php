<?php

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Object;

/**
 * Class SeriesCalculator
 */
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

    /**
     * SeriesCalculator constructor.
     * @param ServiceTask $serviceTask
     * @param YearCalculator $yearCalculator
     */
    public function __construct(ServiceTask $serviceTask, YearCalculator $yearCalculator) {
        $this->serviceTask = $serviceTask;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelContest $contest
     * @return int
     */
    public function getCurrentSeries(ModelContest $contest) {
        $year = $this->yearCalculator->getCurrentYear($contest);
        $currentSeries = $this->serviceTask->getTable()->where([
                    'contest_id' => $contest->contest_id,
                    'year' => $year,
                    '(submit_deadline < ? OR submit_deadline IS NULL)' => new Nette\DateTime()
                ])->max('series');
        return ($currentSeries === null) ? 1 : $currentSeries;
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return int
     */
    public function getLastSeries(ModelContest $contest, $year) {
        $row = $this->serviceTask->getTable()->where([
                    'contest_id' => $contest->contest_id,
                    'year' => $year
                ])->max('series');
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
