<?php

declare(strict_types=1);

namespace FKSDB\Models\Schedule\PaymentDeadlineStrategy;

use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Nette\Utils\DateTime;

class ConstantIntervalStrategy implements PaymentDeadlineStrategy
{
    private \DateInterval $interval;
    private \DateTime $hardDeadline;

    public function __construct(\DateInterval $interval, \DateTime $hardDeadline)
    {
        $this->interval = $interval;
        $this->hardDeadline = $hardDeadline;
    }

    /**
     * @throws \Exception
     */
    public function invoke(ScheduleItemModel $item): ?DateTime
    {
        if (!$item->payable) {
            return null;
        }
        $deadline = new DateTime();
        $deadline->add($this->interval);
        $minDeadline = min($deadline, $this->hardDeadline);
        return new DateTime($minDeadline->format('Y-m-d H:i:s'));
    }
}
