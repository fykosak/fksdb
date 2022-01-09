<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Models\Transitions\Machine;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\Price;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read int person_id
 * @property-read ActiveRow person
 * @property-read int payment_id
 * @property-read ActiveRow event
 * @property-read int event_id
 * @property-read string state
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
class ModelPayment extends AbstractModel implements Resource
{

    public const STATE_WAITING = 'waiting'; // waiting for confirm payment
    public const STATE_RECEIVED = 'received'; // payment received
    public const STATE_CANCELED = 'canceled'; // payment canceled
    public const STATE_NEW = 'new'; // new payment
    public const RESOURCE_ID = 'event.payment';

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getEvent(): ModelEvent
    {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return ModelPersonSchedule[]
     */
    public function getRelatedPersonSchedule(): array
    {
        $query = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id');
        $items = [];
        /** @var ModelSchedulePayment $row */
        foreach ($query as $row) {
            $items[] = ModelPersonSchedule::createFromActiveRow($row->person_schedule);
        }
        return $items;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function getPaymentId(): string
    {
        return \sprintf('%d%04d', $this->event_id, $this->payment_id);
    }

    public function canEdit(): bool
    {
        return \in_array($this->state, [Machine\AbstractMachine::STATE_INIT, self::STATE_NEW]);
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

    public function __toArray(): array
    {
        return [
            'personId' => $this->person_id,
            'paymentId' => $this->payment_id,
            'paymentUId' => $this->getPaymentId(),
            'state' => $this->state,
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
