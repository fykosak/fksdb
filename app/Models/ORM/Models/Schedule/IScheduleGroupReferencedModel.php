<?php

namespace FKSDB\Models\ORM\Models\Schedule;

/**
 * Interface IScheduleGroupReferencedModel
 * *
 */
interface IScheduleGroupReferencedModel {
    public function getScheduleGroup(): ?ModelScheduleGroup;
}
