<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;
/**
 * Class GameLangRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class GameLangRow  extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Game language');
    }
}
