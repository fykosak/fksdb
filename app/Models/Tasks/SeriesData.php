<?php

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelTask;

/**
 * "POD" to hold series pipeline processing data.
 */
class SeriesData
{

    private ModelContestYear $contestYear;
    private int $series;
    private \SimpleXMLElement $data;

    /**
     * @var ModelTask[]
     */
    private array $tasks = [];

    public function __construct(ModelContestYear $contestYear, int $series, \SimpleXMLElement $data)
    {
        $this->contestYear = $contestYear;
        $this->series = $series;
        $this->data = $data;
    }

    public function getContestYear(): ModelContestYear
    {
        return $this->contestYear;
    }

    public function getSeries(): int
    {
        return $this->series;
    }

    public function getData(): \SimpleXMLElement
    {
        return $this->data;
    }

    /**
     * @return ModelTask[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function addTask(int $taskNr, ModelTask $task): void
    {
        $this->tasks[$taskNr] = $task;
    }
}
