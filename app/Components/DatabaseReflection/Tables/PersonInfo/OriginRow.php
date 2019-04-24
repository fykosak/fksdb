<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class OriginField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class OriginRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Jak jsi se o nás dozvěděl(a)?');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}
