<?php

namespace FKSDB\Models\Stats;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceTask;
use Nette\Database\Connection;

class StatsModelFactory {

    private Connection $connection;

    private ServiceTask $serviceTask;

    public function __construct(Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    public function createTaskStatsModel(ModelContest $contest, int $year): TaskStatsModel {
        return new TaskStatsModel($contest, $year, $this->connection);
    }
}
