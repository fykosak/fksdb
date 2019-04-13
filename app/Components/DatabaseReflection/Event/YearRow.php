<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class YearRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class YearRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Contests year');
    }
}
