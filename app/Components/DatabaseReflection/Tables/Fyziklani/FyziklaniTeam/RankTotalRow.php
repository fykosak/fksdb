<?php


namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class RankTotalRow
 * *
 */
class RankTotalRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Total rank');
    }

    protected function getModelAccessKey(): string {
        return 'rank_total';
    }
}
