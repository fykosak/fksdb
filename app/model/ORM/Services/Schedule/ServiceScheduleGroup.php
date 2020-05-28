<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;

/**
 * Class ServiceScheduleGroup
 * *
 */
class ServiceScheduleGroup extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelScheduleGroup::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_GROUP;
    }
}
