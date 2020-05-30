<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;

/**
 * Class ServiceScheduleItem
 * *
 */
class ServiceScheduleItem extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelScheduleItem::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_ITEM;
    }
}
