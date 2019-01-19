<?php

namespace FKSDB\ORM;

use FKSDB\Transitions\IStateModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use ORM\IService;

/**
 * Class FKSDB\ORM\ModelEventPersonAccommodation
 * @property integer person_id
 * @property integer event_accommodation_id
 * @property integer event_person_accommodation_id
 * @property string status
 * @property ActiveRow person
 * @property ActiveRow event_accommodation
 *
 */
class ModelEventPersonAccommodation extends \AbstractModelSingle implements IStateModel {

    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting';

    public function getEventAccommodation(): ModelEventAccommodation {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function getPaymentsAccommodation(): Selection {
        return $this->related(\DbNames::TAB_PAYMENT_ACCOMMODATION, 'event_person_accommodation_id');//->where('payment.state !=', ModelPayment::STATE_CANCELED);
    }

    public function getLabel() {
        $eventAcc = $this->getEventAccommodation();
        $date = clone $eventAcc->date;
        $fromDate = $date->format('d. m.');
        $toDate = $date->add(new \DateInterval('P1D'))->format('d. m. Y');
        return \sprintf(_('Ubytovaní pre osobu %s od %s do %s v hoteli %s'), $this->getPerson()->getFullName(), $fromDate, $toDate, $eventAcc->name);
    }

    public function __toString() {
        return $this->getLabel();
    }

    public function updateState($newState) {
        $this->update(['status' => $newState]);
    }

    public function getState() {
        return $this->status;
    }

    /**
     * @return ModelPayment
     */
    public function refresh(): IStateModel {
        return self::createFromTableRow($this->getTable()->wherePrimary($this->event_person_accommodation_id)->fetch());
    }
}
