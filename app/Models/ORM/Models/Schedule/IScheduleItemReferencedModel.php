<?php

namespace FKSDB\Models\ORM\Models\Schedule;

/**
 * Interface IScheduleItemReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IScheduleItemReferencedModel {
    public function getScheduleItem(): ?ModelScheduleItem;
}
