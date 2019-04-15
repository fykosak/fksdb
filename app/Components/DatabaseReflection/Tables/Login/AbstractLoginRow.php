<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractLoginRow
 * @package FKSDB\Components\DatabaseReflection\Login
 */
abstract class AbstractLoginRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
