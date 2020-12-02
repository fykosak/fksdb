<?php

namespace FKSDB\ORM\Services\Warehouse;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Warehouse\ModelProducer;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceProducer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceProducer extends AbstractServiceSingle {
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_WAREHOUSE_PRODUCER, ModelProducer::class);
    }
}
