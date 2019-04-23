<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class OrgIdRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class OrgIdRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Org Id');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
