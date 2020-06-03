<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class GameLangRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO rendering
 */
class GameLangRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Game language');
    }

    protected function getModelAccessKey(): string {
        return 'game_lang';
    }
}
