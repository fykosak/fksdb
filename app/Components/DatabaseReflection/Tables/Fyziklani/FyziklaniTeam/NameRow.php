<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

/**
 * Class NameRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class NameRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Team name');
    }
}
