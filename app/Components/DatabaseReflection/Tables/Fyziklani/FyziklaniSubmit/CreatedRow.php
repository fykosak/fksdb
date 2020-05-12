<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit
 */
class CreatedRow extends AbstractFyziklaniSubmitRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('H:m:s d-M-Y'))($model->created);
    }
}
