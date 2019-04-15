<?php

namespace FKSDB\Components\DatabaseReflection\Login;
/**
 * Class LoginIdRow
 * @package FKSDB\Components\DatabaseReflection\Login
 */
class LoginIdRow extends AbstractLoginRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Login Id');
    }
}
