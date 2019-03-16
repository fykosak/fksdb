<?php

use FKSDB\ORM\Models\ModelContest;

/**
 * Description of ResultsModelFactory
 *
 * @author michal
 */
class StatsModelFactory {

    /**
     * @var \Nette\Database\Connection
     */
    private $connection;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * StatsModelFactory constructor.
     * @param \Nette\Database\Connection $connection
     * @param ServiceTask $serviceTask
     */
    public function __construct(\Nette\Database\Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return TaskStatsModel
     */
    public function createTaskStatsModel(ModelContest $contest, $year) {
        return new TaskStatsModel($contest, $year, $this->connection);
    }

}


