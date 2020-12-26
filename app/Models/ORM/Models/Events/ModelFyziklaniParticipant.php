<?php

namespace FKSDB\Models\ORM\Models\Events;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow event_participant
 * @property-read int event_participant_id
 * @property-read int e_fyziklani_team_id
 * @property-read ActiveRow e_fyziklani_team
 */
class ModelFyziklaniParticipant extends AbstractModelSingle {

    public function getEventParticipant(): ModelEventParticipant {
        return ModelEventParticipant::createFromActiveRow($this->event_participant);
    }

    public function getFyziklaniTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromActiveRow($this->e_fyziklani_team);
    }
}
