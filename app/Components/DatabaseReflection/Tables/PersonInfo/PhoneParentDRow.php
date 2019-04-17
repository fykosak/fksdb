<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

/**
 * Class PhoneParentDField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentDRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Telefonní číslo (otec)');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
