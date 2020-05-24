<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelStudyYear;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStudyYear extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelStudyYear::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_STUDY_YEAR;
    }
}
