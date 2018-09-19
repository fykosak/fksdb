<?php

use Nette\Database\Table\ActiveRow;

/**
 * Class ModelEventPersonAccommodation
 * @property integer person_id
 * @property integer event_accommodation_id
 * @property integer event_person_accommodation_id
 * @property string status
 * @property ActiveRow person
 * @property ActiveRow event_accommodation
 *
 */
class ModelEventPersonAccommodation extends \AbstractModelSingle {

    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting';

    /**
     * @return ModelEventAccommodation
     */
    public function getEventAccommodation() {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->person);
    }
}
