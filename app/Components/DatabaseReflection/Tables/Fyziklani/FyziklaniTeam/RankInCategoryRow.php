<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class RankInCategoryRow
 * *
 */
class RankInCategoryRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Rank in category');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'rank_category';
    }
}
