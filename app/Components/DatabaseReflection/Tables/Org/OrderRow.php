<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class OrderRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class OrderRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Order');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
