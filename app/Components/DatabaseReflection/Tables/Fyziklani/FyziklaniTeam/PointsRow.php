<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class PointsRow
 * *
 */
class PointsRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Points');
    }

    protected function getModelAccessKey(): string {
        return 'points';
    }
}
