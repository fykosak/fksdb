<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPostContact;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePostContact extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelPostContact::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_POST_CONTACT;
    }
}

