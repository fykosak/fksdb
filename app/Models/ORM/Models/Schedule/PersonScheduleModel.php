<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;

/**
 * @property-read PersonModel $person
 * @property-read ScheduleItemModel $schedule_item
 * @property-read int $person_id
 * @property-read int $schedule_item_id
 * @property-read int $person_schedule_id
 */
class PersonScheduleModel extends Model
{
    public function getPayment(): ?PaymentModel
    {
        /** @var SchedulePaymentModel|null $schedulePayment */
        $schedulePayment = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'person_schedule_id')->fetch();
        return $schedulePayment ? $schedulePayment->payment : null;
    }

    public function getLabel(string $lang): string
    {
        return $this->person->getFullName() . ': '
            . $this->schedule_item->schedule_group->getName()[$lang] . ' - '
            . $this->schedule_item->getName()[$lang];
    }
}
