<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;


use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class LinkedinIdField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class LinkedinIdRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Linkedin Id');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

}
