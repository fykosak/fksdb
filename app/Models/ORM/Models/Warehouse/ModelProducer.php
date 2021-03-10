<?php

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Security\Resource;

/**
 * Class ProducerModel
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read int producer_id
 * @property-read string name
 */
class ModelProducer extends AbstractModelSingle implements Resource {
    public const RESOURCE_ID = 'warehouse.producer';

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
