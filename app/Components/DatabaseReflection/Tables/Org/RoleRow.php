<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class RoleRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class RoleRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Role');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
