<?php

namespace Tasks;

use ModelContest;

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
     * @var array[tasknr] of ModelTask
     */
    private $tasks = array();

    function __construct(ModelContest $contest, $year, $series, $language, $data) {
        $this->contest = $contest;
        $this->year = $year;
        $this->series = $series;
        $this->language = $language;
        $this->data = $data;
    }

    public function getContest() {
        return $this->contest;
    }

    public function getYear() {
        return $this->year;
    }

    public function getSeries() {
        return $this->series;
    }

    public function getData() {
        return $this->data;
    }

    function getLanguage() {
        return $this->language;
    }

    public function getTasks() {
        return $this->tasks;
    }

    public function addTask($tasknr, $task) {
        $this->tasks[$tasknr] = $task;
    }

}
