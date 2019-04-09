<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class EmployerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class EmployerRow extends AbstractRow {

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Zaměstnavatel');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 512;
    }

}
