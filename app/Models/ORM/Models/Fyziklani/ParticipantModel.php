<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow event_participant
 * @property-read int event_participant_id
 * @property-read int e_fyziklani_team_id
 * @property-read ActiveRow e_fyziklani_team
 * @deprecated
 */
class ParticipantModel extends Model
{

    public function getEventParticipant(): ModelEventParticipant
    {
        return ModelEventParticipant::createFromActiveRow($this->event_participant);
    }

    public function getFyziklaniTeam(): TeamModel
    {
        return TeamModel::createFromActiveRow($this->e_fyziklani_team);
    }
}
