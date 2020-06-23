<?php

namespace FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class QIDRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class QIDRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('QID');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->qid);
    }
}
