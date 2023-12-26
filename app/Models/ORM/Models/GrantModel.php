<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read int $role_id
 * @property-read RoleModel $role
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 */
final class GrantModel extends Model
{
}
