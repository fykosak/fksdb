<?php

namespace FKSDB\ORM\Models\Warehouse;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 * Class ProducerModel
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read int producer_id
 * @property-read string name
 */
class ModelProducer extends AbstractModelSingle implements IResource {
    public const RESOURCE_ID = 'warehouse.producer';

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

    public function getProducts(): GroupedSelection {
        return $this->related(DbNames::TAB_WAREHOUSE_PRODUCT);
    }
}
