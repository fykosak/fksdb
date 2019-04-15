<?php


namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

/**
 * Class RankTotalRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class RankTotalRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Total rank');
    }
}
