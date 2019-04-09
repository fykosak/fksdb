<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneParentMField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentMRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Telefonní číslo (matka)');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 128;
    }
}
