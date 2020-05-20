<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelTsafParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTsafParticipant extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelTsafParticipant::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_E_TSAF_PARTICIPANT;
    }
}

