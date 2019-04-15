<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class NoteField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class NoteRow extends AbstractRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Poznámka');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
