<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class NoteField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class NoteRow extends AbstractRow {

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Poznámka');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1;
    }
}
