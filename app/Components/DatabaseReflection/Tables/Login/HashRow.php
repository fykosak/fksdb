<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\ValuePrinters\HashPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Utils\Html;

/**
 * Class HashRow
 * *
 */
class HashRow extends AbstractLoginRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Password');
    }

    /**
     * @param AbstractModelSingle|ModelLogin $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new HashPrinter())($model->hash);
    }
}
