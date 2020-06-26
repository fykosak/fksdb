<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\EmailRowTrait;

/**
 * Class EmailField
 * *
 */
class EmailRow extends AbstractColumnFactory {
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
