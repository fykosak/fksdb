<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AcademicDegreeSuffixField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AcademicDegreeSuffixRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Titul za jménem');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
