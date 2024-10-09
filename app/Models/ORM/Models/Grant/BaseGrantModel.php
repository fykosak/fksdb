<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Grant;

use FKSDB\Models\Authorization\Roles\Base\ExplicitBaseRole;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read ExplicitBaseRole::* $role
 */
final class BaseGrantModel extends Model
{
}
