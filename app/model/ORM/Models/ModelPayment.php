<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Payment\IPaymentModel;
use FKSDB\Payment\Price;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\UnavailableTransitionException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use function in_array;
use function sprintf;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read integer person_id
 * @property-read ActiveRow person
 * @property-read integer payment_id
 * @property-read ActiveRow event
 * @property-read integer event_id
 * @property-read string state
 * @property-read float price
 * @property-read string currency
 * @property-read DateTime created
 * @property-read DateTime received
 * @property-read string constant_symbol
 * @property-read string variable_symbol
 * @property-read string specific_symbol
 * @property-read string bank_account
 * @property-read string bank_name
 * @property-read string recipient
 * @property-read string iban
 * @property-read string swift
 */
class ModelPayment extends AbstractModelSingle implements IResource, IStateModel, IEventReferencedModel, IPaymentModel, IPersonReferencedModel {
    const STATE_WAITING = 'waiting'; // waiting for confirm payment
    const STATE_RECEIVED = 'received'; // payment received
    const STATE_CANCELED = 'canceled'; // payment canceled
    const STATE_NEW = 'new'; // new payment

    const RESOURCE_ID = 'event.payment';

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return ModelPersonSchedule[]
     */
    public function getRelatedPersonSchedule(): array {
        $query = $this->related(DbNames::TAB_SCHEDULE_PAYMENT, 'payment_id');
        $items = [];
        foreach ($query as $row) {
            $items[] = ModelPersonSchedule::createFromActiveRow($row->person_schedule);
        }
        return $items;
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string {
        return sprintf('%d%04d', $this->event_id, $this->payment_id);
    }

    /**
     * @return bool
     */
    public function canEdit(): bool {
        return in_array($this->getState(), [Machine::STATE_INIT, self::STATE_NEW]);
    }

    /**
     * @return Price
     */
    public function getPrice(): Price {
        return new Price($this->price, $this->currency);
    }

    /**
     * @return bool
     */
    public function hasGeneratedSymbols(): bool {
        return $this->constant_symbol || $this->variable_symbol || $this->specific_symbol || $this->bank_account || $this->bank_name || $this->recipient;
    }

    /**
     * @param $newState
     */
    public function updateState($newState) {
        $this->update(['state' => $newState]);
    }

    /**
     * @return null|string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return ModelPayment
     */
    public function refresh(): IStateModel {
        $query = new TypedTableSelection(self::class, DbNames::TAB_PAYMENT, $this->getTable()->getConnection());
        return $query->get($this->getPrimary());
    }
}
