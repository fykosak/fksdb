<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelRole;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRole extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelRole::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_ROLE;
    }
}
