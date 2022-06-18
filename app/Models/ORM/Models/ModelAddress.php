<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int address_id
 * @property-read string target
 * @property-read string postal_code
 * @property-read string city
 * @property-read ActiveRow region
 * @property-read int region_id
 */
class ModelAddress extends Model
{

    public function getRegion(): ?ModelRegion
    {
        return $this->region_id ? ModelRegion::createFromActiveRow($this->region) : null;
    }
}
