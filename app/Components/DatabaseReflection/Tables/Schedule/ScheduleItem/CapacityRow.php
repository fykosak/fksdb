<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;


/**
 * Class CapacityRow
 * *
 */
class CapacityRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Total capacity');
    }

    protected function getModelAccessKey(): string {
        return 'capacity';
    }
}
