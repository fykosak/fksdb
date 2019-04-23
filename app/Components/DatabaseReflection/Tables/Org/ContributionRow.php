<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class ContributionRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContributionRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contribution');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
