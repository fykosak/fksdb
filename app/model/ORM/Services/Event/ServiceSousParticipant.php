<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelSousParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSousParticipant extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelSousParticipant::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_E_SOUS_PARTICIPANT;
    }
}
