<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;

/**
 * Interface IFyziklaniTeamReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IFyziklaniTeamReferencedModel {
    public function getFyziklaniTeam(): ModelFyziklaniTeam;
}
