<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class PersonIdRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person Id');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
