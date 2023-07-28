<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\CountryModel;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<CountryModel>
 */
final class CountryService extends Service
{
    public const CZECH_REPUBLIC = 203;
    public const SLOVAKIA = 703;
}
