<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\RegionModel;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\NetteORM\Service;

/**
 * @method RegionModel findByPrimary($key)
 */
class RegionService extends Service
{
    public function getCountries(): TypedSelection
    {
        return $this->getTable()->where('country_iso = nuts');
    }
}
