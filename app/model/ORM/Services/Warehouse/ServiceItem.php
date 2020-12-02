<?php

namespace FKSDB\ORM\Services\Warehouse;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Warehouse\ModelItem;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceItem
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceItem extends AbstractServiceSingle{
    public function __construct(Context $connection, IConventions $conventions) {
    parent::__construct($connection, $conventions, DbNames::TAB_WAREHOUSE_ITEM, ModelItem::class);
}
}
