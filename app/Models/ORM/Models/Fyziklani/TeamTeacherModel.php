<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow person
 * @property-read int person_id
 * @property-read int fyziklani_team_id
 * @property-read ActiveRow fyziklani_team
 */
class TeamTeacherModel extends Model
{

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getFyziklaniTeam(): TeamModel2
    {
        return TeamModel2::createFromActiveRow($this->fyziklani_team);
    }
}
