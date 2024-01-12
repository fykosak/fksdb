<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $event_grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read int $event_role_id
 * @property-read EventRoleModel $event_role
 * @property-read int $event_id
 * @property-read EventModel $event
 */
class EventGrantModel extends Model
{
}
