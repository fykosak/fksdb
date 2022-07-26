<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read PaymentModel payment
 * @property-read ModelPersonSchedule person_schedule
 * @property-read int person_schedule_id
 */
class ModelSchedulePayment extends Model
{
}
