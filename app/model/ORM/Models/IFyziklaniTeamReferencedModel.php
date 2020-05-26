<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;

/**
 * Interface IFyziklaniTeamReferencedModel
 * *
 */
interface IFyziklaniTeamReferencedModel {
    public function getFyziklaniTeam(): ModelFyziklaniTeam;
}
