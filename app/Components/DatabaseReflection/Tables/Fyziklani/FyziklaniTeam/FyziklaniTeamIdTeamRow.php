<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class FyziklaniTeamIdTeamRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class FyziklaniTeamIdTeamRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Team Id');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'e_fyziklani_team_id';
    }
}
