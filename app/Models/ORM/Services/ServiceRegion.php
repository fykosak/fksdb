<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends AbstractServiceSingle {

    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
