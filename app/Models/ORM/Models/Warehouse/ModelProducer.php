<?php

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\AbstractModel;
use Nette\Security\Resource;

/**
 * @property-read int producer_id
 * @property-read string name
 */
class ModelProducer extends AbstractModel implements Resource {
    public const RESOURCE_ID = 'warehouse.producer';

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
