<?php


class ServicePaymentAccommodation extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_PAYMENT_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\ModelPaymentAccommodation';
}
