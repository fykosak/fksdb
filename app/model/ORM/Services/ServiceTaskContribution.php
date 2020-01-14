<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelTaskContribution;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTaskContribution extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelTaskContribution::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_TASK_CONTRIBUTION;
    }
}
