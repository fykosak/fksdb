<?php

use FKSDB\ORM\ModelContest;

/**
 * General results sheet with contestants and their ranks.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TaskStatsModel {

    /**
     * @var int
     */
    protected $year;

    /**
     * @var ModelContest
     */
    protected $contest;

    /**
     * @var \Nette\Database\Connection
     */
    protected $connection;

    /**
     * @var int
     */
    protected $series;

    /**
     * TaskStatsModel constructor.
     * @param ModelContest $contest
     * @param $year
     * @param \Nette\Database\Connection $connection
     */
    function __construct(ModelContest $contest, $year, \Nette\Database\Connection $connection) {
        $this->contest = $contest;
        $this->connection = $connection;
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getSeries() {
        return $this->series;
    }

    /**
     * @param $series
     */
    public function setSeries($series) {
        $this->series = $series;
    }

    /**
     * @param array $labels of string
     * @return array of Nette\Database\Row (rows from view_task_stats)
     */
    public function getData($labels) {
        $sql = "SELECT * FROM `v_task_stats` WHERE " .
                "contest_id = ? AND year = ? " .
                "AND series = ? AND label IN ('" . implode("','", $labels) . "')";

        $stmt = $this->connection->query($sql, $this->contest->contest_id, $this->year, $this->series);
        $result = $stmt->fetchAll();

        return $result;
    }

}


