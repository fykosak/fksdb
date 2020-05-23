<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\EmailRowTrait;

/**
 * Class EmailParentMRow
 * @package FKSDB\Components\DatabaseReflection\PersonInfo
 */
class EmailParentMRow extends AbstractRow {
    use EmailRowTrait;

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    public function getTitle(): string {
        return _('E-mail (matka)');
    }

    public function getModelAccessKey(): string {
        return 'email_parent_m';
    }
}
