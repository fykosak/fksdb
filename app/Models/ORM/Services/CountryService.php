<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Models\RegionModel;
use Fykosak\NetteORM\Service;

/**
 * @method CountryModel|null findByPrimary(int $key)
 */
class CountryService extends Service
{
    public const CZECH_REPUBLIC = 203;
    public const SLOVAKIA = 703;
}
