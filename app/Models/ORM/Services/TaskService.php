<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\Service;

class TaskService extends Service
{

    public function findBySeries(ContestYearModel $contestYear, int $series, int $taskNumber): ?TaskModel
    {
        return $contestYear->contest->related(DbNames::TAB_TASK)->where([
            'year' => $contestYear->year,
            'series' => $series,
            'tasknr' => $taskNumber,
        ])->fetch();
    }
}
