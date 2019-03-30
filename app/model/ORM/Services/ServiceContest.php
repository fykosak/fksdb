<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContest extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelContest::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_CONTEST;
    }
}

