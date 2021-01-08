<?php

namespace FKSDB\Model\ORM\Models\Fyziklani;

use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\DeprecatedLazyModel;
use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition
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
    use DeprecatedLazyModel;

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
