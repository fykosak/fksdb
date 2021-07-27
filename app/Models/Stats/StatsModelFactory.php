<?php

namespace FKSDB\Models\Stats;

use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\Database\Connection;

class StatsModelFactory {

    private Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function createTaskStatsModel(ModelContestYear $contestYear): TaskStatsModel {
        return new TaskStatsModel($contestYear, $this->connection);
    }
}
