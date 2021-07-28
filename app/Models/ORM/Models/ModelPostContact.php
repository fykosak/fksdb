<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow address
 */
class ModelPostContact extends AbstractModel {

    public const TYPE_DELIVERY = 'D';
    public const TYPE_PERMANENT = 'P';

    public function getAddress(): ?ModelAddress {
        return $this->address ? ModelAddress::createFromActiveRow($this->address) : null;
    }
}
