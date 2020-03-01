<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;

/**
 * Interface IFyziklaniTeamReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IFyziklaniTeamReferencedModel {
    /**
     * @return ModelFyziklaniTeam
     */
    public function getFyziklaniTeam();
}
