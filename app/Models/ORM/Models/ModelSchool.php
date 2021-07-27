<?php

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read ActiveRow address
 * @property-read string name_abbrev
 * @property-read int school_id
 * @property-read bool|int active
 * @property-read string izo
 */
class ModelSchool extends AbstractModel implements Resource {

    public const RESOURCE_ID = 'school';

    public function getAddress(): ModelAddress {
        return ModelAddress::createFromActiveRow($this->address);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
