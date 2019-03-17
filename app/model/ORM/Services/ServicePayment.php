<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePayment extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PAYMENT;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPayment';
}
