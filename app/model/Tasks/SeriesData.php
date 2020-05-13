<?php

namespace FKSDB\Tasks;

use FKSDB\ORM\Models\ModelContest;

/**
 * "POD" to hold series pipeline processing data.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesData {

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $series;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var array[tasknr] of FKSDB\ORM\Models\ModelTask
     */
    private $tasks = [];

    /**
     * SeriesData constructor.
     * @param ModelContest $contest
     * @param $year
     * @param $series
     * @param $data
     */
    public function __construct(ModelContest $contest, $year, $series, $data) {
        $this->contest = $contest;
        $this->year = $year;
        $this->series = $series;
        $this->data = $data;
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        return $this->contest;
    }

    /**
     * @return int
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getSeries() {
        return $this->series;
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getTasks() {
        return $this->tasks;
    }

    /**
     * @param $tasknr
     * @param $task
     */
    public function addTask($tasknr, $task) {
        $this->tasks[$tasknr] = $task;
    }

}
