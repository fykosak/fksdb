<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Nette\DI\Container;

class GroupAttendanceFormComponent extends AttendanceFormComponent
{
    protected ScheduleGroupModel $group;

    public function __construct(Container $container, ScheduleGroupModel $group)
    {
        parent::__construct($container);
        $this->group = $group;
    }

    protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel
    {
        return $person->getScheduleByGroup($this->group);
    }
}
