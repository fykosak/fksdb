<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Login;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\HashPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelLogin;
use Nette\Utils\Html;

/**
 * Class HashRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HashRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelLogin $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new HashPrinter())($model->hash);
    }
}
