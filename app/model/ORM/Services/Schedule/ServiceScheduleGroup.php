<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * Class ServiceScheduleGroup
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceScheduleGroup extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_SCHEDULE_GROUP;
    protected $modelClassName = 'FKSDB\ORM\Models\Schedule\ModelScheduleGroup';
}
