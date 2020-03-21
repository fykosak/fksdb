<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractEventRowFactory
 * @package FKSDB\Components\DatabaseReflection\Event
 */
abstract class AbstractEventRowFactory extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
