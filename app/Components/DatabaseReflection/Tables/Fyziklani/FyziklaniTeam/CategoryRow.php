<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

/**
 * Class CategoryRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class CategoryRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Category');
    }
}
