<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelRegion;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

/**
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends AbstractService
{

    public function getCountries(): TypedTableSelection
    {
        return $this->getTable()->where('country_iso = nuts');
    }
}
