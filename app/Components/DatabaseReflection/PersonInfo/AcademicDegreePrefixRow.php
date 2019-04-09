<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

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
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
