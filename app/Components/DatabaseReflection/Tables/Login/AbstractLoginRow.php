<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractLoginRow
 * *
 */
abstract class AbstractLoginRow extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }
}
