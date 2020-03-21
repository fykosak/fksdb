<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractTeacherRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
abstract class AbstractTeacherRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}