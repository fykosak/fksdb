<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class GameLangRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO rendering
 */
class GameLangRow extends AbstractFyziklaniTeamRow {

    public function getTitle(): string {
        return _('Game language');
    }

    protected function getModelAccessKey(): string {
        return 'game_lang';
    }
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
