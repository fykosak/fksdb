<?php

declare(strict_types=1);

namespace FKSDB\Models\Schedule;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Nette\Utils\DateTime;

class ConstantStrategy extends TimeoutStrategy
{
    public function __construct(string $modifier)
    {
    }

    public function getDeadline(PersonScheduleModel $personSchedule): DateTime
    {
        $personSchedule
    }
}
