<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ScheduleGroupTypeRow
 * *
 */
class ScheduleGroupTypeRow extends AbstractScheduleGroupRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Type');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'schedule_group_type';
    }
}
