<?php

namespace FKSDB\Models\Schedule;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Nette\Utils\DateTime;

abstract class TimeoutStrategy
{
    abstract public function getDeadline(PersonScheduleModel $personSchedule): DateTime;
}
