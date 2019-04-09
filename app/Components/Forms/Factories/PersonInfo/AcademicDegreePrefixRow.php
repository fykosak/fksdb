<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class AcademicDegreePrefixField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AcademicDegreePrefixRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Titul před jménem');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 512;
    }
}
