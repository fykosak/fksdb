<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class CreatedRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
