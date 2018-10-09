<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow address
 */
class ModelSchool extends AbstractModelSingle implements IResource {

    public function getAddress(): ModelAddress {
        $data = $this->address;
        return ModelAddress::createFromTableRow($data);
    }

    public function getResourceId(): string {
        return 'school';
    }

}
