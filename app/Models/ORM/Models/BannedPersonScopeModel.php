<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $banned_person_scope_id
 * @property-read int $banned_person_id
 * @property-read BannedPersonModel $bannedPerson
 * @property-read int|null $event_type_id
 * @property-read EventTypeModel|null $event_type
 * @property-read int|null $contest_id
 * @property-read ContestModel|null $contest
 * @property-read DateTime $begin
 * @property-read DateTime|null $end
 */
class BannedPersonScopeModel extends Model
{
}
