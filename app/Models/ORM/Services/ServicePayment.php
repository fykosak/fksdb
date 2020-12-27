<?php

namespace FKSDB\Models\ORM\Services;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelPayment refresh(AbstractModelSingle $model)
 */
class ServicePayment extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PAYMENT, ModelPayment::class);
    }
}
