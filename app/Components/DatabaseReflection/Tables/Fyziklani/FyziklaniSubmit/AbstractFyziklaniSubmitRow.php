<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractFyziklaniSubmitRow
 * *
 */
abstract class AbstractFyziklaniSubmitRow extends AbstractRow {
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
