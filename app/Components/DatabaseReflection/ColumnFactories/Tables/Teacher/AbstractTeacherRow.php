<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;

/**
 * Class AbstractTeacherRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractTeacherRow extends AbstractColumnFactory {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
