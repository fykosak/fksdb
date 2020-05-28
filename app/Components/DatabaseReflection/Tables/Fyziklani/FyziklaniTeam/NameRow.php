<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NameRow
 * *
 */
class NameRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Team name');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'name';
    }
}
