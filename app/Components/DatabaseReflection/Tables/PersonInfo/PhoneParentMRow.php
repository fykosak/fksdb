<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractPhoneRow;

/**
 * Class PhoneParentMField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentMRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Telefonní číslo (matka)');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
