<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelTask;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTask extends AbstractServiceSingle {

    public function findBySeries(ModelContest $contest, int $year, int $series, int $tasknr): ?ModelTask {
        /** @var ModelTask $result */
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
        ])->fetch();
        return $result;
    }
}
