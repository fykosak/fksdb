<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelRegion;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\NetteORM\Service;

/**
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends Service
{

    public function getCountries(): TypedSelection
    {
        return $this->getTable()->where('country_iso = nuts');
    }
}
