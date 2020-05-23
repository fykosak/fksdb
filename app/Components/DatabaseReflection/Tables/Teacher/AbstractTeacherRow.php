<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractTeacherRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
abstract class AbstractTeacherRow extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
