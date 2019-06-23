<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\Transitions\IStateModel;
use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\ORM\Models\ModelEventPersonAccommodation
 * @property-read integer person_id
 * @property-read integer event_accommodation_id
 * @property-read integer event_person_accommodation_id
 * @property-read string status
 * @property-read ActiveRow person
 * @property-read ActiveRow event_accommodation
 *
 */
class ModelEventPersonAccommodation extends AbstractModelSingle implements IStateModel, IPersonReferencedModel {

    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting';

    /**
     * @return ModelEventAccommodation
     */
    public function getEventAccommodation(): ModelEventAccommodation {
        return ModelEventAccommodation::createFromActiveRow($this->event_accommodation);
    }

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return ModelPayment|null
     */
    public function getPayment() {
        $data = $this->related(DbNames::TAB_PAYMENT_ACCOMMODATION, 'event_person_accommodation_id')->select('payment.*')->fetch();
        if (!$data) {
            return null;
        }
        return ModelPayment::createFromActiveRow($data);
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
        return self::createFromActiveRow($this->getTable()->wherePrimary($this->event_person_accommodation_id)->fetch());
    }
}
