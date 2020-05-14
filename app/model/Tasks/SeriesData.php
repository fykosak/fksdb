<?php

namespace FKSDB\Tasks;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;

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
     * @var \SimpleXMLElement
     */
    private $data;

    /**
     * array[tasknr] of FKSDB\ORM\Models\ModelTask
     * @var ModelTask[]
     */
    private $tasks = [];

    /**
     * SeriesData constructor.
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     * @param \SimpleXMLElement $data
     */
    public function __construct(ModelContest $contest, int $year, int $series, \SimpleXMLElement $data) {
        $this->contest = $contest;
        $this->year = $year;
        $this->series = $series;
        $this->data = $data;
    }

    public function getContest(): ModelContest {
        return $this->contest;
    }

    public function getYear(): int {
        return $this->year;
    }

    public function getSeries(): int {
        return $this->series;
    }

    public function getData(): \SimpleXMLElement {
        return $this->data;
    }

    /**
     * @return ModelTask[]
     */
    public function getTasks(): array {
        return $this->tasks;
    }

    /**
     * @param int $taskNr
     * @param ModelTask $task
     */
    public function addTask(int $taskNr, ModelTask $task) {
        $this->tasks[$taskNr] = $task;
    }
}
