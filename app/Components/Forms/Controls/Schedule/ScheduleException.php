<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Nette\InvalidStateException;

class ScheduleException extends InvalidStateException
{
    public ?ScheduleGroupModel $group;

    public function __construct(?ScheduleGroupModel $group, string $message)
    {
        parent::__construct($message);
        $this->group = $group;
    }
}
