<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\EmailRowTrait;

/**
 * Class EmailField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class EmailRow extends AbstractRow {
    use EmailRowTrait;

    public function getTitle(): string {
        return _('E-mail');
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_RESTRICT;
    }

    public function getModelAccessKey(): string {
        return 'email';
    }
}
