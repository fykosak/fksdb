<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelRegion;
use Fykosak\NetteORM\TypedTableSelection;
use Fykosak\NetteORM\AbstractService;

/**
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends AbstractService {

    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
