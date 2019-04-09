<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Telefonní číslo');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 128;
    }
}
