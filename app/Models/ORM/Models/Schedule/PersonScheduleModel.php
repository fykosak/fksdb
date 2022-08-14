<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PaymentState;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;

/**
 * @property-read PersonModel person
 * @property-read ScheduleItemModel schedule_item
 * @property-read int person_id
 * @property-read int schedule_item_id
 * @property-read string state
 * @property-read int person_schedule_id
 */
class PersonScheduleModel extends Model
{
    public function getPayment(): ?PaymentModel
    {
        $data = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'person_schedule_id')->select('payment.*')->fetch();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function hasActivePayment(): bool
    {
        $payment = $this->getPayment();
        if (!$payment) {
            return false;
        }
        if ($payment->state->value == PaymentState::CANCELED) {
            return false;
        }
        return true;
    }

    /**
     * @throws NotImplementedException
     */
    public function getLabel(): string
    {
        $item = $this->schedule_item;
        $group = $item->schedule_group;
        switch ($group->schedule_group_type->value) {
            case ScheduleGroupType::ACCOMMODATION:
                return sprintf(
                    _('Accommodation for %s from %s to %s in %s'),
                    $this->person->getFullName(),
                    $group->start->format(_('__date')),
                    $group->end->format(_('__date')),
                    $item->name_cs
                );
            case ScheduleGroupType::WEEKEND:
                return $item->getLabel();
            default:
                throw new NotImplementedException();
        }
    }
}
