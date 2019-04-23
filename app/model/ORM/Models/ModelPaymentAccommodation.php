<?php


namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelPaymentAccommodation
 * @package FKSDB\ORM
 * @property-readActiveRow payment
 * @property-readint payment_id
 * @property-readActiveRow event_person_accommodation
 * @property-readint event_person_accommodation_id
 * @property-readint payment_accommodation_id
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
