<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractTeacherRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractTeacherRow extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
