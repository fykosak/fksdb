<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractOrgRowFactory
 * @package FKSDB\Components\DatabaseReflection\Org
 */
abstract class AbstractOrgRowFactory extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
