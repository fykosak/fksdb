<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class CreatedRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }
}
