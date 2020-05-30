<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class FyziklaniTeamIdTeamRow
 * *
 */
class FyziklaniTeamIdRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Team Id');
    }

    protected function getModelAccessKey(): string {
        return 'e_fyziklani_team_id';
    }
}
