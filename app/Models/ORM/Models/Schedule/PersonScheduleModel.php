<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\Security\Resource;

/**
 * @property-read PersonModel $person
 * @property-read ScheduleItemModel $schedule_item
 * @property-read int $person_id
 * @property-read int $schedule_item_id
 * @property-read int $person_schedule_id
 * @property-read PersonScheduleState|null $state
 */
final class PersonScheduleModel extends Model implements Resource
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

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    public function checkPayment(): void
    {
        if (
            $this->schedule_item->isPayable() &&
            (!$this->getPayment() || $this->getPayment()->state->value !== PaymentState::RECEIVED)
        ) {
            throw new BadRequestException(_('Payment not found'));
        }
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
                $value = PersonScheduleState::tryFrom($value);
                break;
        }
        return $value;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
