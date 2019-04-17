<?php


namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Utils\Html;

/**
 * Class LastLoginRow
 * @package FKSDB\Components\DatabaseReflection\Login
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
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter)($model->last_login,'d.m.Y H:i:s');
    }
}
