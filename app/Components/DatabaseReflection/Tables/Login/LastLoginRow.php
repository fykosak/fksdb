<?php


namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Utils\Html;

/**
 * Class LastLoginRow
 * *
 */
class LastLoginRow extends AbstractLoginRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Last logged');
    }

    /**
     * @param AbstractModelSingle|ModelLogin $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter('d.m.Y H:i:s'))($model->last_login);
    }
}
