<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Utils\Html;

/**
 * Class GameStartRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup
 */
class ResultDisplayRow extends AbstractFyziklaniGameSetupRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Results display');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniGameSetup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d. m. Y H:i:s'))($model->result_display);
    }
}
