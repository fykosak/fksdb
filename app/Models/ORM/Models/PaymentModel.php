<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\Price;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read int payment_id
 * @property-read EventModel event
 * @property-read int event_id
 * @property-read PaymentState state
 * @property-read float price
 * @property-read string currency
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface received
 * @property-read string constant_symbol
 * @property-read string variable_symbol
 * @property-read string specific_symbol
 * @property-read string bank_account
 * @property-read string bank_name
 * @property-read string recipient
 * @property-read string iban
 * @property-read string swift
 */
class PaymentModel extends Model implements Resource
{
    public const RESOURCE_ID = 'event.payment';

    /**
     * @return PersonScheduleModel[]
     */
    public function getRelatedPersonSchedule(): array
    {
        $items = [];
        /** @var SchedulePaymentModel $row */
        foreach ($this->getSchedulePayment() as $row) {
            $items[] = $row->person_schedule;
        }
        return $items;
    }

    public function getSchedulePayment(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id');
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function canEdit(): bool
    {
        return $this->state->value == PaymentState::IN_PROGRESS;
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
     * @param string $key
     * @return PaymentState|FakeStringEnum|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = PaymentState::tryFrom($value);
                break;
        }
        return $value;
    }

    public function __toArray(): array
    {
        return [
            'personId' => $this->person_id,
            'paymentId' => $this->payment_id,
            'state' => $this->state->value,
            'price' => $this->price,
            'currency' => $this->currency,
            'constantSymbol' => $this->constant_symbol,
            'variableSymbol' => $this->variable_symbol,
            'specificSymbol' => $this->specific_symbol,
            'bankAccount' => $this->bank_account,
            'bankName' => $this->bank_name,
            'recipient' => $this->recipient,
            'iban' => $this->iban,
            'swift' => $this->swift,
        ];
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
