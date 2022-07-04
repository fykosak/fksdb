<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow address
 */
class ModelPostContact extends Model
{
    public function getAddress(): ?ModelAddress
    {
        return $this->address ? ModelAddress::createFromActiveRow($this->address) : null;
    }
}
