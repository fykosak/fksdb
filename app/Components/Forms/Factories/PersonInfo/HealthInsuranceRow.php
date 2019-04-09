<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;

/**
 * Class HealthInsuranceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HealthInsuranceRow extends AbstractRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Zdravotní pojišťovna');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 512;
    }
}
