<?php

namespace Tasks;

use ModelContest;
use SimpleXMLElement;

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
     * @var SimpleXMLElement
     */
    private $XML;

    /**
     *
     * @var array[tasknr] of ModelTask
     */
    private $tasks = array();

    public function __construct(ModelContest $contest, $year, $series, SimpleXMLElement $XML) {
        $this->contest = $contest;
        $this->year = $year;
        $this->series = $series;
        $this->XML = $XML;
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

    public function getXML() {
        return $this->XML;
    }

    public function getTasks() {
        return $this->tasks;
    }

    public function addTask($tasknr, $task) {
        $this->tasks[$tasknr] = $task;
    }

}
