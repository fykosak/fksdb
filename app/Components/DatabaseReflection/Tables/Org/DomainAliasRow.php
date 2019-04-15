<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class DomainAliasRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class DomainAliasRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Domain alias');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
