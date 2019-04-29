<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ApplicationStateTrait;

/**
 * Class StatusRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class StatusRow extends AbstractFyziklaniTeamRow {
    use ApplicationStateTrait;

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'status';
    }
}
