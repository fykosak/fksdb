<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\AddressModel;
use Fykosak\NetteORM\Service;

/**
 * @method AddressModel findByPrimary($key)
 */
class AddressService extends Service
{
    private const PATTERN = '/[0-9]{5}/';
}
