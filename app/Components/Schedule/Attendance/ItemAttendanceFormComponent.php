<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\DI\Container;

class ItemAttendanceFormComponent extends AttendanceFormComponent
{
    protected ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item)
    {
        parent::__construct($container);
        $this->item = $item;
    }

    protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel
    {
        return $person->getScheduleByItem($this->item);
    }
}
