<?php

namespace FKSDB\ORM\Services\Warehouse;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Warehouse\ModelProduct;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceProduct
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceProduct extends AbstractServiceSingle {
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_WAREHOUSE_PRODUCT, ModelProduct::class);
    }
}
