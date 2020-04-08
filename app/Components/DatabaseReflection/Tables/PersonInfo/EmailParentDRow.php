<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\EmailRowTrait;

/**
 * Class EmailParentDRow
 * @package FKSDB\Components\DatabaseReflection\PersonInfo
 */
class EmailParentDRow extends AbstractRow {
    use EmailRowTrait;

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('E-mail (otec)');
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'email_parent_d';
    }
}
