<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;
/**
 * Class PointsRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PointsRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Points');
    }
}
