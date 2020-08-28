<?php

namespace FKSDB\Stats;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Database\Connection;

/**
 * Description of ResultsModelFactory
 *
 * @author michal
 */
class StatsModelFactory {

    private Connection $connection;

    private ServiceTask $serviceTask;

    /**
     * StatsModelFactory constructor.
     * @param Connection $connection
     * @param ServiceTask $serviceTask
     */
    public function __construct(Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    public function createTaskStatsModel(ModelContest $contest, int $year): TaskStatsModel {
        return new TaskStatsModel($contest, $year, $this->connection);
    }
}
