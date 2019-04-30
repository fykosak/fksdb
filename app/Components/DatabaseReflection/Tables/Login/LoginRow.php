<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class LoginRow
 * @package FKSDB\Components\DatabaseReflection\Login
 */
class LoginRow extends AbstractLoginRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Login');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'login';
    }
}
