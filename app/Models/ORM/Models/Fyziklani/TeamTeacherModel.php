<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;

/**
 * @property-read int $fyziklani_team_teacher_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $fyziklani_team_id
 * @property-read TeamModel2 $fyziklani_team
 */
final class TeamTeacherModel extends Model
{
}
