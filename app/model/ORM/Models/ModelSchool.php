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
 */
class ModelSchool extends AbstractModelSingle implements IResource {
    const RESOURCE_ID = 'school';

    /**
     * @return ModelAddress
     */
    public function getAddress(): ModelAddress {
        return ModelAddress::createFromActiveRow($this->address);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

}
