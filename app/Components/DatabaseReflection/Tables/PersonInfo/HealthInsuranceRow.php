<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class HealthInsuranceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HealthInsuranceRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Zdravotní pojišťovna');
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }
}
