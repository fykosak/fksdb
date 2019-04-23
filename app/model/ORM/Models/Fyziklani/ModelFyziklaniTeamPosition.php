<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition
 * @property-readinteger room_id
 * @property-readinteger e_fyziklani_team_id
 * @property-readinteger row
 * @property-readinteger col
 * @property-readActiveRow room
 */
class ModelFyziklaniTeamPosition extends AbstractModelSingle {
    /**
     * @return ModelFyziklaniRoom
     */
    public function getRoom(): ModelFyziklaniRoom {
        return ModelFyziklaniRoom::createFromTableRow($this->room);
    }
}
