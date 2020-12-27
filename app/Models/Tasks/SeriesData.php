<?php

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelTask;

/**
 * "POD" to hold series pipeline processing data.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesData {

    private ModelContest $contest;
    private int $year;
    private int $series;
    private \SimpleXMLElement $data;

    /**
     * @var ModelTask[]
     */
    private array $tasks = [];

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

    public function addTask(int $taskNr, ModelTask $task): void {
        $this->tasks[$taskNr] = $task;
    }
}
