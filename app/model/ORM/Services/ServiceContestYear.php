<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestYear;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestYear extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelContestYear::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_CONTEST_YEAR;
    }
}
