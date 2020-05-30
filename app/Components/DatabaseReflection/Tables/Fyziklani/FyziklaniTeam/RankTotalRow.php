<?php


namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class RankTotalRow
 * *
 */
class RankTotalRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Total rank');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'rank_total';
    }
}
