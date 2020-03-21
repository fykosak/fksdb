<?php

namespace FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class QIDRow
 * @package FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery
 */
class QIDRow extends AbstractRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('QID');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'qid';
    }
}
