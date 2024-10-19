<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\InvalidStateException;

class RequiredIdNumberException extends InvalidStateException
{
    public function __construct(
        public readonly PersonModel $person,
        public readonly ScheduleItemModel $item
    ) {
        parent::__construct();
    }
}
