<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ModelPerson person
 * @property-read int person_id
 * @property-read int fyziklani_team_id
 * @property-read TeamModel2 fyziklani_team
 */
class TeamTeacherModel extends Model
{
}
