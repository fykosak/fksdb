<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\HashPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Utils\Html;

/**
 * Class HashRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HashRow extends AbstractColumnFactory {

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

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_RESTRICT, self::PERMISSION_ALLOW_RESTRICT);
    }
}
