<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class ForceARow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class ForceARow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Force A');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new BinaryPrinter)($model->force_a);
    }
}
