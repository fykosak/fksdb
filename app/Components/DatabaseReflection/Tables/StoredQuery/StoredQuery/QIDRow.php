<?php

namespace FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class QIDRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class QIDRow extends AbstractRow {
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
