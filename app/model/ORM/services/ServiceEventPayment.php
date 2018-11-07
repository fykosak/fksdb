<?php

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceEventPayment extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_PAYMENT;
    protected $modelClassName = 'FKSDB\ORM\ModelEventPayment';
}
