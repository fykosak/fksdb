<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Grant;

use FKSDB\Models\Authorization\Roles\Events\ExplicitEventRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $event_grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string|ExplicitEventRole::* $role
 * @property-read int $event_id
 * @property-read EventModel $event
 */
final class EventGrantModel extends Model
{
}
