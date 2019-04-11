<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

/**
 * Class PhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneRow extends AbstractPhoneRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Phone number');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
