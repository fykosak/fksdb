<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;

/**
 * Class ServicePersonSchedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePersonSchedule extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PERSON_SCHEDULE;
    }
}
