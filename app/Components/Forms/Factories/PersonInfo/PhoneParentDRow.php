<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneParentDField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentDRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Telefonní číslo (otec)');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 128;
    }
}
