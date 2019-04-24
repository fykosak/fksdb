<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;

/**
 * Class ServiceScheduleItem
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceScheduleItem extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelScheduleItem::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_ITEM;
    }
}
