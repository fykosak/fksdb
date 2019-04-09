<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class AcademicDegreeSuffixField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AcademicDegreeSuffixRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Titul za jménem');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 512;
    }
}
