<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelTaskStudyYear;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTaskStudyYear extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelTaskStudyYear::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_TASK_STUDY_YEAR;
    }
}
