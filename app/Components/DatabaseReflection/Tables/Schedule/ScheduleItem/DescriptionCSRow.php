<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DescriptionCSRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class DescriptionCSRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Description cs');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'description_cs';
    }
}