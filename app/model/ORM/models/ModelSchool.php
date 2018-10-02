<?php

use Nette\Security\IResource;
use Nette\Database\Table\ActiveRow;
/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow address
 */
class ModelSchool extends AbstractModelSingle implements IResource {

    /**
     * @return ModelAddress
     */
    public function getAddress() {
        $data = $this->address;
        return ModelAddress::createFromTableRow($data);
    }

    public function getResourceId() {
        return 'school';
    }

}
