<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class FamilyNameRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class FamilyNameRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Family name');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
