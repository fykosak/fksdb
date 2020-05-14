<?php

namespace FKSDB\ORM\Services\Events;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelDsefParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefParticipant extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelDsefParticipant::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_E_DSEF_PARTICIPANT;
    }
}
