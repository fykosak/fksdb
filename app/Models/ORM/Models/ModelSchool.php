<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read ModelAddress address
 * @property-read string name_abbrev
 * @property-read int school_id
 * @property-read bool|int active
 * @property-read string izo
 */
class ModelSchool extends Model implements Resource
{

    public const RESOURCE_ID = 'school';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
