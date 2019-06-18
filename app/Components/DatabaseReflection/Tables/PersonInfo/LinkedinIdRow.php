<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;


use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class LinkedinIdField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class LinkedinIdRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Linkedin Id');
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
        return 'linkedin_id';
    }

}
