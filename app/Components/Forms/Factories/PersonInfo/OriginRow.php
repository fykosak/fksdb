<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class OriginField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class OriginRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Jak jsi se o nás dozvěděl(a)?');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 1;
    }
}
