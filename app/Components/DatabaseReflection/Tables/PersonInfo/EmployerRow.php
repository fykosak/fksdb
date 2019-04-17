<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class EmployerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class EmployerRow extends AbstractRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Zaměstnavatel');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

}
