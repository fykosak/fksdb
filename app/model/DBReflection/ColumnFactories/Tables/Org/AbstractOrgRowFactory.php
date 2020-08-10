<?php

namespace FKSDB\DBReflection\ColumnFactories\Org;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;

/**
 * Class AbstractOrgRowFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractOrgRowFactory extends AbstractColumnFactory {
    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
