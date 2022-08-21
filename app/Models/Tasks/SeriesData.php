<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * "POD" to hold series pipeline processing data.
 */
class SeriesData
{
    private ContestYearModel $contestYear;
    private int $series;
    private \SimpleXMLElement $data;

    /**
     * @var TaskModel[]
     */
    private array $tasks = [];

    public function __construct(ContestYearModel $contestYear, int $series, \SimpleXMLElement $data)
    {
        $this->contestYear = $contestYear;
        $this->series = $series;
        $this->data = $data;
    }

    public function getContestYear(): ContestYearModel
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
     * @return TaskModel[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function addTask(int $taskNr, TaskModel $task): void
    {
        $this->tasks[$taskNr] = $task;
    }
}
