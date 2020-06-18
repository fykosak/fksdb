<?php

namespace FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class QIDRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class QIDRow extends AbstractColumnFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('QID');
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    protected function getModelAccessKey(): string {
        return 'qid';
    }
}
