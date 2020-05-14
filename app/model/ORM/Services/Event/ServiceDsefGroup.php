<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelDsefGroup;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefGroup extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelDsefGroup::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_E_DSEF_GROUP;
    }
}
