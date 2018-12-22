<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\SymbolGenerator\AlreadyGeneratedSymbolsException;
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
 */
class ModelPayment extends AbstractModelSingle implements IResource, IStateModel {
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

    /**
     * @return ModelEventPersonAccommodation[]
     */
    public function getRelatedPersonAccommodation() {
        $query = $this->related(\DbNames::TAB_PAYMENT_ACCOMMODATION, 'payment_id');
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
     * @throws \FKSDB\Transitions\UnavailableTransitionException
     * @throws AlreadyGeneratedSymbolsException
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function executeTransition(Machine $machine, $id) {
        $machine->executeTransition($id, $this);
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

    public function updateState($newState) {
        $this->update(['state' => $newState]);
    }

    public function getState() {
        return $this->state;
    }

    public function updatePrice(PriceCalculator $priceCalculator) {
        $priceCalculator->setCurrency($this->currency);
        $price = $priceCalculator->execute($this);

        $this->update([
            'price' => $price->getAmount(),
            'currency' => $price->getCurrency(),
        ]);
    }
}
