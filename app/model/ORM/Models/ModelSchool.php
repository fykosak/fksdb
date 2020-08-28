<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow address
 * @property-read string name_abbrev
 * @property-read int school_id
 * @property-read bool|int active
 */
class ModelSchool extends AbstractModelSingle implements IResource {
    public const RESOURCE_ID = 'school';

    public function getAddress(): ModelAddress {
        return ModelAddress::createFromActiveRow($this->address);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
