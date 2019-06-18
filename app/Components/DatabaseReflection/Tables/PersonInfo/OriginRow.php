<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class OriginField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class OriginRow extends AbstractRow {
    use DefaultPrinterTrait;

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

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'origin';
    }
}
