<?php

namespace FKSDB\Stats;

use FKSDB\ORM\Models\ModelContest;
use Nette\Database\Connection;
use Nette\Database\Row;

/**
 * General results sheet with contestants and their ranks.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TaskStatsModel {

    protected int $year;

    protected ModelContest $contest;

    protected Connection $connection;

    /**
     * @var int
     */
    protected $series;

    /**
     * TaskStatsModel constructor.
     * @param ModelContest $contest
     * @param $year
     * @param Connection $connection
     */
    public function __construct(ModelContest $contest, int $year, Connection $connection) {
        $this->contest = $contest;
        $this->connection = $connection;
        $this->year = $year;
    }

    public function getSeries(): int {
        return $this->series;
    }

    public function setSeries(int $series): void {
        $this->series = $series;
    }

    /**
     * @param string[] $labels
     * @return Row[]
     */
    public function getData(array $labels): array {
        $sql = "SELECT * FROM `v_task_stats` WHERE " .
            "contest_id = ? AND year = ? " .
            "AND series = ? AND label IN ('" . implode("','", $labels) . "')";

        $stmt = $this->connection->query($sql, $this->contest->contest_id, $this->year, $this->series);
        return $stmt->fetchAll();
    }
}
