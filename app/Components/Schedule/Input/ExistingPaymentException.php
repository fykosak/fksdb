<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;

class ExistingPaymentException extends ScheduleException
{
    public function __construct(PersonScheduleModel $personSchedule)
    {
        parent::__construct($personSchedule->schedule_item->schedule_group, _('Item has a assigned a payment'));
    }
}
