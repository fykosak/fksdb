<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractOrgRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractOrgRowFactory extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
