<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;

/**
 * Class ServicePersonSchedule
 * @package FKSDB\ORM\Services\Schedule
 */
class ServicePersonSchedule extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_SCHEDULE;
    }
}
