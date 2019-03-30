<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePayment extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPayment::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PAYMENT;
    }
}
