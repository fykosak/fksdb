<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition
 * @property-read int room_id
 * @property-read int e_fyziklani_team_id
 * @property-read int row
 * @property-read int col
 * @property-read ActiveRow e_fyziklani_team
 * @property-read ActiveRow room
 * @property-read double x_coordinate
 * @property-read double y_coordinate
 */
class ModelFyziklaniTeamPosition extends AbstractModelSingle {

    public function getRoom(): ModelFyziklaniRoom {
        return ModelFyziklaniRoom::createFromActiveRow($this->room);
    }

    public function getTeam(): ?ModelFyziklaniTeam {
        if ($this->e_fyziklani_team_id) {
            return ModelFyziklaniTeam::createFromActiveRow($this->e_fyziklani_team);
        }
        return null;
    }
}
