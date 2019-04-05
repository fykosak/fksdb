<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelLogin;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelLogin::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_LOGIN;
    }
}
