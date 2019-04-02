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
 */
class ModelSchool extends AbstractModelSingle implements IResource {
    /**
     * @return ModelAddress
     */
    public function getAddress(): ModelAddress {
        return ModelAddress::createFromTableRow($this->address);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'school';
    }

}
