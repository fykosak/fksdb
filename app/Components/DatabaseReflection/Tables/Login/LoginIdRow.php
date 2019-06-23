<?php

namespace FKSDB\Components\DatabaseReflection\Login;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class LoginIdRow
 * @package FKSDB\Components\DatabaseReflection\Login
 */
class LoginIdRow extends AbstractLoginRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Login Id');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'login_id';
    }
}
