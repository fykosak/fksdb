<?php

namespace FKSDB\ORM\Models\Fyziklani;

use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition
 * @property integer room_id
 * @property integer e_fyziklani_team_id
 * @property integer row
 * @property integer col
 * @property ActiveRow room
 */
class ModelFyziklaniTeamPosition extends \AbstractModelSingle {
    /**
     * @return ModelFyziklaniRoom
     */
    public function getRoom(): ModelFyziklaniRoom {
        return ModelFyziklaniRoom::createFromTableRow($this->room);
    }
}
