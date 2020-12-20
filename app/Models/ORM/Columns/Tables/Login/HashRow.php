<?php

namespace FKSDB\Models\ORM\Columns\Tables\Login;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\HashPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelLogin;
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
