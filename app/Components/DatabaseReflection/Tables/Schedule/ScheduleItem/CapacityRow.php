<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;


/**
 * Class CapacityRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class CapacityRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Total capacity');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'capacity';
    }
}