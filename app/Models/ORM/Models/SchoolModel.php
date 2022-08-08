<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int school_id
 * @property-read string name_full
 * @property-read string name
 * @property-read string name_abbrev
 * @property-read int address_id
 * @property-read AddressModel address
 * @property-read string email
 * @property-read string ic
 * @property-read string izo
 * @property-read bool active
 * @property-read string note
 */
class SchoolModel extends Model implements Resource
{

    public const RESOURCE_ID = 'school';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
