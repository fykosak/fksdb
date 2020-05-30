<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;

/**
 * Interface IScheduleGroupReferencedModel
 * *
 */
interface IScheduleGroupReferencedModel {
    public function getScheduleGroup(): ModelScheduleGroup;
}
