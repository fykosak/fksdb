<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Models\PaymentModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $payment_id
 * @property-read PaymentModel $payment
 * @property-read int $person_schedule_id
 * @property-read PersonScheduleModel $person_schedule
 */
final class SchedulePaymentModel extends Model
{
}
