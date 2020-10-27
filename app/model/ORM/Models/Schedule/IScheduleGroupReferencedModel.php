<?php

namespace FKSDB\ORM\Models\Schedule;

/**
 * Interface IScheduleGroupReferencedModel
 * *
 */
interface IScheduleGroupReferencedModel {
    public function getScheduleGroup(): ?ModelScheduleGroup;
}
