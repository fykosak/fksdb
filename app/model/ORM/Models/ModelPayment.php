<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Transitions\IEventReferencedModel;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use Nette\Database\Table\ActiveRow;
use Nette\DateTime;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property integer person_id
 * @property ActiveRow person
 * @property integer payment_id
 * @property ActiveRow event
 * @property integer event_id
 * @property string state
 * @property float price
 * @property string currency
 * @property DateTime created
 * @property DateTime received
 * @property string constant_symbol
 * @property string variable_symbol
 * @property string specific_symbol
 * @property string bank_account
 * @property string iban
 * @property string swift
 */
class ModelPayment extends AbstractModelSingle implements IResource, IStateModel, IEventReferencedModel {
    const STATE_WAITING = 'waiting'; // waiting for confirm payment
    const STATE_RECEIVED = 'received'; // payment received
    const STATE_CANCELED = 'canceled'; // payment canceled
    const STATE_NEW = 'new'; // new payment

    const RESOURCE_ID = 'event.payment';

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    /**
     * @return ModelEventPersonAccommodation[]
     */
    public function getRelatedPersonAccommodation(): array {
        $query = $this->related(DbNames::TAB_PAYMENT_ACCOMMODATION, 'payment_id');
        $items = [];
        foreach ($query as $row) {
            $items[] = ModelEventPersonAccommodation::createFromTableRow($row->event_person_accommodation);
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
     * @param Machine $machine
     * @param $id
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function executeTransition(Machine $machine, $id) {
        $machine->executeTransition($id, $this);
    }

    /**
     * @return string
     */
    public function getPaymentId(): string {
        return \sprintf('%d%04d', $this->event_id, $this->payment_id);
    }

    /**
     * @return bool
     */
    public function canEdit(): bool {
        return \in_array($this->getState(), [Machine::STATE_INIT, self::STATE_NEW]);
    }

    /**
     * @return \FKSDB\Payment\Price
     */
    public function getPrice(): Price {
        return new Price($this->price, $this->currency);
    }

    /**
     * @return bool
     */
    public function hasGeneratedSymbols(): bool {
        return $this->constant_symbol || $this->variable_symbol || $this->specific_symbol || $this->bank_account;
    }

    /**
     * @return string
     */
    public function getUIClass(): string {
        $class = 'badge ';
        switch ($this->state) {
            case ModelPayment::STATE_WAITING:
                $class .= 'badge-warning';
                break;
            case ModelPayment::STATE_CANCELED:
                $class .= 'badge-secondary';
                break;
            case ModelPayment::STATE_RECEIVED:
                $class .= 'badge-success';
                break;
            case ModelPayment::STATE_NEW:
                $class .= 'badge-primary';
                break;
            default:
                $class .= 'badge-light';
        }
        return $class;
    }

    /**
     * @return string
     */
    public function getStateLabel() {
        switch ($this->state) {
            case ModelPayment::STATE_NEW:
                return _('New payment');

            case ModelPayment::STATE_WAITING:
                return _('Waiting for paying');

            case ModelPayment::STATE_CANCELED:
                return _('Payment canceled');

            case ModelPayment::STATE_RECEIVED:
                return _('Payment received');
            default:
                return $this->state;
        }
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
     * @param PriceCalculator $priceCalculator
     */
    public function updatePrice(PriceCalculator $priceCalculator) {
        $priceCalculator->setCurrency($this->currency);
        $price = $priceCalculator->execute($this);

        $this->update([
            'price' => $price->getAmount(),
            'currency' => $price->getCurrency(),
        ]);
    }

    /**
     * @return ModelPayment
     */
    public function refresh(): IStateModel {
        return self::createFromTableRow($this->getTable()->wherePrimary($this->payment_id)->fetch());
    }
}
