<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class SinceRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class SinceRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Since');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
