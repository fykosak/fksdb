<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read PersonModel $person
 * @property-read ScheduleItemModel $schedule_item
 * @property-read int $person_id
 * @property-read int $schedule_item_id
 * @property-read int $person_schedule_id
 * @property-read \DateTime|null $payment_deadline
 * @property-read PersonScheduleState $state
 */
final class PersonScheduleModel extends Model implements EventResource
{
    public const RESOURCE_ID = 'event.schedule.person';

    public function getPayment(): ?PaymentModel
    {
        /** @var SchedulePaymentModel|null $schedulePayment */
        $schedulePayment = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'person_schedule_id')->fetch();
        return $schedulePayment ? $schedulePayment->payment : null;
    }

    public function getLabel(Language $lang): string
    {
        return $this->person->getFullName() . ': '
            . $this->schedule_item->schedule_group->name->getText($lang->value) . ' - '
            . $this->schedule_item->name->getText($lang->value);
    }

    public function isPaid(): bool
    {
        if (!$this->schedule_item->payable) {
            return true; // ak sa nedá zaplatiť je zaplatená
        }
        $payment = $this->getPayment();
        if (!$payment) {
            return false;
        }
        return $payment->state->value === PaymentState::RECEIVED;
    }

    /**
     * @return PersonScheduleState|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = PersonScheduleState::from($value ?? PersonScheduleState::Applied);
                break;
        }
        return $value;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function getEvent(): EventModel
    {
        return $this->schedule_item->schedule_group->event;
    }
}
