<?php

namespace FKSDB\ORM;

use Nette\Database\Table\ActiveRow;

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
class ModelEventPersonAccommodation extends \AbstractModelSingle {

    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting';

    public function getEventAccommodation(): ModelEventAccommodation {
        return ModelEventAccommodation::createFromTableRow($this->event_accommodation);
    }

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }
}
