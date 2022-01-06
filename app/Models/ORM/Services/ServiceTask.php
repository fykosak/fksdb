<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\AbstractService;

class ServiceTask extends AbstractService {

    public function findBySeries(ModelContestYear $contestYear, int $series, int $taskNumber): ?ModelTask {
        $row = $contestYear->getContest()->related(DbNames::TAB_TASK)->where([
            'year' => $contestYear->year,
            'series' => $series,
            'tasknr' => $taskNumber,
        ])->fetch();
        return $row ? ModelTask::createFromActiveRow($row) : null;
    }
}
