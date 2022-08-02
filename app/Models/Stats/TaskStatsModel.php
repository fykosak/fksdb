<?php

declare(strict_types=1);

namespace FKSDB\Models\Stats;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Database\Explorer;
use Nette\Database\Row;

class TaskStatsModel
{
    protected ContestYearModel $contestYear;
    protected Explorer $explorer;
    public int $series;

    public function __construct(ContestYearModel $contestYear, Explorer $explorer)
    {
        $this->contestYear = $contestYear;
        $this->explorer = $explorer;
    }

    /**
     * @param string[] $labels
     * @return Row[]
     * @throws \PDOException
     */
    public function getData(array $labels): array
    {
        return $this->explorer->query(
            'SELECT * FROM `v_task_stats` WHERE contest_id = ? AND year = ? AND series = ? AND label IN ?',
            $this->contestYear->contest_id,
            $this->contestYear->year,
            $this->series,
            $labels
        )->fetchAll();
    }
}
