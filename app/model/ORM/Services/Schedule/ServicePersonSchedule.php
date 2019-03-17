<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * Class ServicePersonSchedule
 * @package FKSDB\ORM\Services\Schedule
 */
class ServicePersonSchedule extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_PERSON_SCHEDULE;
    protected $modelClassName = 'FKSDB\ORM\Models\Schedule\ModelPersonSchedule';
}
