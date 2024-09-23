<?php

declare(strict_types=1);

namespace FKSDB\Models\Schedule\PaymentDeadlineStrategy;

use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\Utils\DateTime;

class ConstantIntervalStrategy implements PaymentDeadlineStrategy
{
    private \DateInterval $interval;

    public function __construct(\DateInterval $interval)
    {
        $this->interval = $interval;
    }

    public function invoke(ScheduleItemModel $item): ?DateTime
    {
        if (!$item->payable) {
            return null;
        }
        $deadline = new DateTime();
        $deadline->add($this->interval);
        return $deadline;
    }
}
