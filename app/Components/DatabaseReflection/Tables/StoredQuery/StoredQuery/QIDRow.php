<?php

namespace FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class QIDRow
 * @package FKSDB\Components\DatabaseReflection\StoredQuery\StoredQuery
 */
class QIDRow extends AbstractRow {
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
}
