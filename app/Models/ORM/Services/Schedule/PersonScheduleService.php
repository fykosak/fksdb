<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use Fykosak\NetteORM\Service;

class PersonScheduleService extends Service
{
    // TODO prijebaný názov
    public function makeAttendance(PersonScheduleModel $personSchedule): void
    {
        $this->storeModel(['state' => PersonScheduleState::PARTICIPATED], $personSchedule);
    }
}
