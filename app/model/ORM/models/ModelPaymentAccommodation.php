<?php


namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelPaymentAccommodation
 * @package FKSDB\ORM
 * @property ActiveRow payment
 * @property int payment_id
 * @property ActiveRow event_person_accommodation
 * @property int event_person_accommodation_id
 * @property int payment_accommodation_id
 */
class ModelPaymentAccommodation extends AbstractModelSingle {
    /**
     * @return ModelPayment
     */
    public function getPayment(): ModelPayment {
        return ModelPayment::createFromTableRow($this->payment);
    }

    /**
     * @return ModelEventPersonAccommodation
     */
    public function getEventPersonAccommodation(): ModelEventPersonAccommodation {
        return ModelEventPersonAccommodation::createFromTableRow($this->event_person_accommodation);
    }

}
