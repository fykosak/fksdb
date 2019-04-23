<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\Transitions\IStateModel;
use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\ORM\Models\ModelEventPersonAccommodation
 * @property-readinteger person_id
 * @property-readinteger event_accommodation_id
 * @property-readinteger event_person_accommodation_id
 * @property-readstring status
 * @property-readActiveRow person
 * @property-readActiveRow event_accommodation
 *
 */
class ModelEventPersonAccommodation extends AbstractModelSingle implements IStateModel {

    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting';

    /**
     * @return ModelEventAccommodation
     */
    public function getEventAccommodation(): ModelEventAccommodation {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return ModelPayment|null
     */
    public function getPayment() {
        $data = $this->related(DbNames::TAB_PAYMENT_ACCOMMODATION, 'event_person_accommodation_id')->select('payment.*')->fetch();
        if (!$data) {
            return null;
        }
        return ModelPayment::createFromTableRow($data);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabel(): string {
        $eventAcc = $this->getEventAccommodation();
        $date = clone $eventAcc->date;
        $fromDate = $date->format('d. m.');
        $toDate = $date->add(new \DateInterval('P1D'))->format('d. m. Y');
        return \sprintf(_('Ubytovaní pre osobu %s od %s do %s v hoteli %s'), $this->getPerson()->getFullName(), $fromDate, $toDate, $eventAcc->name);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString(): string {
        return $this->getLabel();
    }

    /**
     * @param string? $newState
     */
    public function updateState($newState) {
        $this->update(['status' => $newState]);
    }

    /**
     * @return null|string
     */
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
