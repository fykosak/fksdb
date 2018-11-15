<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use FKSDB\EventPayment\Transition\Machine;
use Nette\Database\Table\ActiveRow;
use Nette\Diagnostics\Debugger;
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
 * @property float price_kc
 * @property float price_eur
 * @property string constant_symbol
 * @property string variable_symbol
 * @property string specific_symbol
 * @property string bank_account
 */
class ModelEventPayment extends AbstractModelSingle implements IResource {
    const STATE_WAITING = 'waiting';
    const STATE_CONFIRMED = 'confirmed';
    const STATE_CANCELED = 'canceled';
    const STATE_NEW = 'new';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
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
            case ModelEventPayment::STATE_CONFIRMED:
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
