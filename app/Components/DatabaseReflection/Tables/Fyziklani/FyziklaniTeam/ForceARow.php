<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class ForceARow
 * *
 */
class ForceARow extends AbstractFyziklaniTeamRow {

    public function getTitle(): string {
        return _('Force A');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new BinaryPrinter())($model->force_a);
    }
}
