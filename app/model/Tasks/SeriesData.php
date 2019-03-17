<?php

namespace Tasks;

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
     * @var string ISO 2 chars
     */
    private $language;

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
     * @param $language
     * @param $data
     */
    function __construct(ModelContest $contest, $year, $series, $language, $data) {
        $this->contest = $contest;
        $this->year = $year;
        $this->series = $series;
        $this->language = $language;
        $this->data = $data;
    }

    /**
     * @return \FKSDB\ORM\Models\ModelContest
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
     * @return string
     */
    function getLanguage() {
        return $this->language;
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
