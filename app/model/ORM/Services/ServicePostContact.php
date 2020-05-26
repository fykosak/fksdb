<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPostContact;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePostContact extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPostContact::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_POST_CONTACT;
    }
}
