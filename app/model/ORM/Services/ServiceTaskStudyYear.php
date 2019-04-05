<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelTaskStudyYear;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTaskStudyYear extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelTaskStudyYear::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_TASK_STUDY_YEAR;
    }
}
