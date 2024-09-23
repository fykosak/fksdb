<?php

declare(strict_types=1);

namespace FKSDB\Models\Schedule\PaymentDeadlineStrategy;

use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\Utils\DateTime;

interface PaymentDeadlineStrategy
{
    public function invoke(ScheduleItemModel $item): ?DateTime;
}
