<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\Models\AbstractModelSingle;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelPayment refresh(AbstractModelSingle $model)
 */
class ServicePayment extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PAYMENT, ModelPayment::class);
    }
}
