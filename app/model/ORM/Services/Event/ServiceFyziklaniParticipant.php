<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelFyziklaniParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceFyziklaniParticipant extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelFyziklaniParticipant::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_E_FYZIKLANI_PARTICIPANT;
    }
}
