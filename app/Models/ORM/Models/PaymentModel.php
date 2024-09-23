<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\Price;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $payment_id
 * @property-read PaymentState $state
 * @property-read float|null $price
 * @property-read string|null $currency
 * @property-read DateTime|null $created
 * @property-read DateTime|null $received
 * @property-read string|null $constant_symbol
 * @property-read string|null $variable_symbol
 * @property-read string|null $specific_symbol
 * @property-read string|null $bank_account
 * @property-read string|null $bank_name
 * @property-read string|null $recipient
 * @property-read string|null $iban
 * @property-read string|null $swift
 * @property-read int $want_invoice
 * @property-read string|null $invoice_id
 */
final class PaymentModel extends Model implements Resource
{
    public const RESOURCE_ID = 'payment';

    /**
     * @phpstan-return TypedGroupedSelection<SchedulePaymentModel>
     */
    public function getSchedulePayment(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<SchedulePaymentModel> $selection */
        $selection = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id');
        return $selection;
    }

    public function getRelatedEvent(): ?EventModel
    {
        $event = null;
        /** @var SchedulePaymentModel $schedulePayment */
        foreach ($this->getSchedulePayment() as $schedulePayment) {
            $newEvent = $schedulePayment->person_schedule->schedule_item->schedule_group->event;
            if ($event && $newEvent->event_id !== $event->event_id) {
                throw new \InvalidArgumentException('Payment related to more than one event');
            }
            if (!$event) {
                $event = $newEvent;
            }
        }
        return $event;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function canEdit(): bool
    {
        return $this->state->value === PaymentState::IN_PROGRESS;
    }

    /**
     * @throws \Exception
     */
    public function getPrice(): Price
    {
        return new Price($this->getCurrency(), $this->price);
    }

    /**
     * @throws \Exception
     */
    public function getCurrency(): Currency
    {
        return Currency::from($this->currency);
    }

    /**
     * @return PaymentState|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = PaymentState::from($value);
                break;
        }
        return $value;
    }

    public function hasGeneratedSymbols(): bool
    {
        return $this->constant_symbol
            || $this->variable_symbol
            || $this->specific_symbol
            || $this->bank_account
            || $this->bank_name
            || $this->recipient;
    }
}
