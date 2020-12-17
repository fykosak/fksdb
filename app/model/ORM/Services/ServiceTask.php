<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\DbNames;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTask extends AbstractServiceSingle {

    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_TASK, ModelTask::class);
    }

    public function findBySeries(ModelContest $contest, int $year, int $series, int $tasknr): ?ModelTask {
        /** @var ModelTask $result */
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
        ])->fetch();
        return $result ?: null;
    }
}
