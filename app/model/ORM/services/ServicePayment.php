<?php

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePayment extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PAYMENT;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPayment';
}
