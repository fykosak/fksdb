<?php

declare(strict_types=1);

namespace FKSDB\Models\Stats;

use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\Database\Connection;
use Nette\Database\Row;

class TaskStatsModel
{
    protected ModelContestYear $contestYear;
    protected Connection $connection;
    protected int $series;

    public function __construct(ModelContestYear $contestYear, Connection $connection)
    {
        $this->contestYear = $contestYear;
        $this->connection = $connection;
    }

    public function getSeries(): int
    {
        return $this->series;
    }

    public function setSeries(int $series): void
    {
        $this->series = $series;
    }

    /**
     * @param string[] $labels
     * @return Row[]
     * @throws \PDOException
     */
    public function getData(array $labels): array
    {
        $sql = 'SELECT * FROM `v_task_stats` WHERE ' .
            'contest_id = ? AND year = ? ' .
            "AND series = ? AND label IN ('" . implode("','", $labels) . "')";

        $stmt = $this->connection->query($sql, $this->contestYear->contest_id, $this->contestYear->year, $this->series);
        return $stmt->fetchAll();
    }
}
