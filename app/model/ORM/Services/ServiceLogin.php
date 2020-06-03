<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelLogin;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelLogin::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_LOGIN;
    }
}
