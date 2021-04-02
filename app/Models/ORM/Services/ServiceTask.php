<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\AbstractService;

class ServiceTask extends AbstractService {

    public function findBySeries(ModelContest $contest, int $year, int $series, int $taskNumber): ?ModelTask {
        $row = $contest->related(DbNames::TAB_TASK)->where([
            'year' => $year,
            'series' => $series,
            'tasknr' => $taskNumber,
        ])->fetch();
        return $row ? ModelTask::createFromActiveRow($row) : null;
    }
}
