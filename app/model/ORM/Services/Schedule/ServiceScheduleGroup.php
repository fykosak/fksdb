<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;

/**
 * Class ServiceScheduleGroup
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceScheduleGroup extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelScheduleGroup::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_GROUP;
    }
}
