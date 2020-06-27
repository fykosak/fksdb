<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelPayment;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePayment extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelPayment::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PAYMENT;
    }
}
