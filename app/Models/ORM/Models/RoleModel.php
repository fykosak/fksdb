<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int $role_id
 * @property-read string $name
 * @property-read string|null $description
 */
final class RoleModel extends Model
{
    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
