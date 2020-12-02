<?php

namespace FKSDB\ORM\Models\Warehouse;

use FKSDB\ORM\Models\AbstractModelSingle;
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
}
