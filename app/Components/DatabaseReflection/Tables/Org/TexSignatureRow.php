<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class TexSignatureRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class TexSignatureRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Tex signature');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

}
