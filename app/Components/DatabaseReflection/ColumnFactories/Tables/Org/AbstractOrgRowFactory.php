<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;

/**
 * Class AbstractOrgRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractOrgRowFactory extends AbstractColumnFactory {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
