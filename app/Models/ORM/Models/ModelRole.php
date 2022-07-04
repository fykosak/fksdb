<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read string name
 */
class ModelRole extends Model
{

    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
