<?php

namespace FKSDB\ORM\Models\Schedule;

/**
 * Interface IScheduleItemReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IScheduleItemReferencedModel {
    public function getScheduleItem(): ?ModelScheduleItem;
}
