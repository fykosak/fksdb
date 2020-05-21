<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPersonHistory;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePersonHistory extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPersonHistory::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PERSON_HISTORY;
    }
}
