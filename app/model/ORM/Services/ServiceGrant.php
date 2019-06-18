<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelGrant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGrant extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelGrant::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_GRANT;
    }
}

