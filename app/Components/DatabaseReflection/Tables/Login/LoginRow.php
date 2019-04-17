<?php

namespace FKSDB\Components\DatabaseReflection\Login;

/**
 * Class LoginRow
 * @package FKSDB\Components\DatabaseReflection\Login
 */
class LoginRow extends AbstractLoginRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Login');
    }
}
