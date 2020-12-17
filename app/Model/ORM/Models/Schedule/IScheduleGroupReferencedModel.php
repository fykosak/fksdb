<?php

namespace FKSDB\Model\ORM\Models\Schedule;

/**
 * Interface IScheduleGroupReferencedModel
 * *
 */
interface IScheduleGroupReferencedModel {
    public function getScheduleGroup(): ?ModelScheduleGroup;
}
