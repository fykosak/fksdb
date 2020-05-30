<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NameRow
 * *
 */
class NameRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Team name');
    }

    protected function getModelAccessKey(): string {
        return 'name';
    }
}
