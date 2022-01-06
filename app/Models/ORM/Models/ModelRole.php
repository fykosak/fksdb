<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read string name
 */
class ModelRole extends AbstractModel
{

    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
