<?php

namespace FKSDB\Model\ORM\Models\Schedule;

/**
 * Interface IScheduleItemReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IScheduleItemReferencedModel {
    public function getScheduleItem(): ?ModelScheduleItem;
}
