<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;

/**
 * Interface IScheduleGroupReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IScheduleGroupReferencedModel {
    public function getScheduleGroup(): ModelScheduleGroup;
}
