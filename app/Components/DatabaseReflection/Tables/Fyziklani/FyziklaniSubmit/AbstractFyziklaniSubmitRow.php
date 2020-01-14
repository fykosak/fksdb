<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class AbstractFyziklaniSubmitRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniSubmit
 */
abstract class AbstractFyziklaniSubmitRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

}
