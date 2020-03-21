<?php

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Database\Connection;

/**
 * Description of ResultsModelFactory
 *
 * @author michal
 */
class StatsModelFactory {

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * StatsModelFactory constructor.
     * @param Connection $connection
     * @param ServiceTask $serviceTask
     */
    public function __construct(Connection $connection, ServiceTask $serviceTask) {
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


