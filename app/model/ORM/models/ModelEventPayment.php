<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\EventPayment\Transition\Machine;
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
 * @property string data
 * @property string state
 * @property float price
 * @property string currency
 * @property DateTime created
 * @property DateTime received
 * @property string constant_symbol
 * @property string variable_symbol
 * @property string specific_symbol
 * @property string bank_account
 */
class ModelEventPayment extends AbstractModelSingle implements IResource {
    const STATE_WAITING = 'waiting'; // waitign for confimr payment
    const STATE_RECEIVED = 'received'; // platba prijatá
    const STATE_CANCELED = 'canceled'; // platba zrušená
    const STATE_NEW = 'new'; // nová platba

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    public function getRelatedPersonAccommodation() {
        $query = $this->related(\DbNames::TAB_EVENT_PAYMENT_TO_PERSON_ACCOMMODATION, 'payment_id');
        $items = [];
        foreach ($query as $row) {
            $items[] = ModelEventPersonAccommodation::createFromTableRow($row->event_person_accommodation);
        }
        return $items;
    }

    public function getResourceId(): string {
        return 'event.payment';
    }

    /**
     * @param Machine $machine
     * @param $id
     * @param $isOrg
     * @throws \FKSDB\EventPayment\Transition\UnavailableTransitionException
     */
    public function executeTransition(Machine $machine, $id, $isOrg) {
        $machine->executeTransition($id, $this, $isOrg);
    }

    /**
     * @return string
     */
    public function getPaymentId(): string {
        return \sprintf('%d%04d', $this->event_id, ($this->payment_id));
    }

    /**
     * @return bool
     */
    public function canEdit(): bool {
        return \in_array($this->state, [self::STATE_NEW]);
    }

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
            case ModelEventPayment::STATE_WAITING:
                $class .= 'badge-warning';
                break;
            case ModelEventPayment::STATE_CANCELED:
                $class .= 'badge-secondary';
                break;
            case ModelEventPayment::STATE_RECEIVED:
                $class .= 'badge-success';
                break;
            case ModelEventPayment::STATE_NEW:
                $class .= 'badge-primary';
                break;
            default:
                $class .= 'badge-light';
        }
        return $class;
    }

}
