<?php

namespace FKSDB\Model\ORM\Models;

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int address_id
 * @property-read string postal_code
 * @property-read string city
 * @property-read ActiveRow region
 * @property-read int region_id
 */
class ModelAddress extends AbstractModelSingle {

    public function getRegion(): ?ModelRegion {
        return $this->region_id ? ModelRegion::createFromActiveRow($this->region) : null;
    }
}
