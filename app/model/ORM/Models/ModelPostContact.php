<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow address
 */
class ModelPostContact extends AbstractModelSingle {
    const TYPE_DELIVERY = 'D';
    const TYPE_PERMANENT = 'P';

    /**
     * @return ModelAddress|null
     */
    public function getAddress() {
        $address = $this->address;
        if ($address) {
            return ModelAddress::createFromActiveRow($address);
        } else {
            return null;
        }
    }

}
