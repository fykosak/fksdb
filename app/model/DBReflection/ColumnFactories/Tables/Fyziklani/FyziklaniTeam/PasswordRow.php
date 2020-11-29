<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\HashPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class PasswordRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PasswordRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new HashPrinter())($model->password);
    }
}
