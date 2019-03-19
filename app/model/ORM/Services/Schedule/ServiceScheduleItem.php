<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * Class ServiceScheduleItem
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceScheduleItem extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_SCHEDULE_ITEM;
    protected $modelClassName = 'FKSDB\ORM\Models\Schedule\ModelScheduleItem';
}
