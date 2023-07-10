<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PaymentModel;

/**
 * @property-read PaymentModel $payment
 * @property-read PersonScheduleModel $person_schedule
 * @property-read int $person_schedule_id
 */
final class SchedulePaymentModel extends Model
{
}
