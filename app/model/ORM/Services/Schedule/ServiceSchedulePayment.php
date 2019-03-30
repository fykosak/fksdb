<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelSchedulePayment;

/**
 * Class ServiceSchedulePayment
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceSchedulePayment extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_PAYMENT;
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelSchedulePayment::class;
    }
}
