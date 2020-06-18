<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;

/**
 * Class AbstractEventRowFactory
 * *
 */
abstract class AbstractEventRowFactory extends AbstractColumnFactory {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
