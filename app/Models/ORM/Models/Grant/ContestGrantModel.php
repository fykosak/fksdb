<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Grant;

use FKSDB\Models\Authorization\Roles\Contest\ExplicitContestRole;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string|ExplicitContestRole::* $role
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 */
final class ContestGrantModel extends Model
{
}
