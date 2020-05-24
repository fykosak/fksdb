<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractEventRowFactory
 * *
 */
abstract class AbstractEventRowFactory extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
