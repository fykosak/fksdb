<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Schedule\ModelScheduleItem;

/**
 * Interface IScheduleItemReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IScheduleItemReferencedModel {
    public function getScheduleItem(): ModelScheduleItem;
}
