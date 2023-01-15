<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PaymentState;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;

/**
 * @property-read PersonModel person
 * @property-read ScheduleItemModel schedule_item
 * @property-read int person_id
 * @property-read int schedule_item_id
 * @property-read int person_schedule_id
 */
class PersonScheduleModel extends Model
{
    public function getPayment(): ?PaymentModel
    {
        /** @var SchedulePaymentModel|null $schedulePayment */
        $schedulePayment = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'person_schedule_id')->fetch();
        return $schedulePayment ? $schedulePayment->payment : null;
    }

    public function hasActivePayment(): bool
    {
        $payment = $this->getPayment();
        return $payment && $payment->state->value !== PaymentState::CANCELED;
    }

    /**
     * @throws NotImplementedException
     */
    public function getLabel(): string
    {
        switch ($this->schedule_item->schedule_group->schedule_group_type->value) {
            case ScheduleGroupType::ACCOMMODATION:
                return sprintf(
                    _('Accommodation for %s from %s to %s in %s'),
                    $this->person->getFullName(),
                    $this->schedule_item->schedule_group->start->format(_('__date')),
                    $this->schedule_item->schedule_group->end->format(_('__date')),
                    $this->schedule_item->name_cs
                );
            case ScheduleGroupType::WEEKEND:
                return $this->schedule_item->getLabel();
            default:
                throw new NotImplementedException();
        }
    }
}
