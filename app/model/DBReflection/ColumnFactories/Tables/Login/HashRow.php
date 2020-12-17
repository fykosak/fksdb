<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Login;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\HashPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
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
