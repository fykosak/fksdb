<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\Model;
use Nette\Security\Resource;

/**
 * @property-read int producer_id
 * @property-read string name
 */
class ProducerModel extends Model implements Resource
{
    public const RESOURCE_ID = 'warehouse.producer';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
