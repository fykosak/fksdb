<?php


class ServicePaymentToAccommodation extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PAYMENT_TO_PERSON_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\ModelPaymentToAccommodation';
}
