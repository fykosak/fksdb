<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventType;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventType extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelEventType::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_EVENT_TYPE;
    }
}
