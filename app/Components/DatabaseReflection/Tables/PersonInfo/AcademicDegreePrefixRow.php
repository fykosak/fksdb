<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class AcademicDegreePrefixField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AcademicDegreePrefixRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Titul před jménem');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'academic_degree_prefix';
    }
}
