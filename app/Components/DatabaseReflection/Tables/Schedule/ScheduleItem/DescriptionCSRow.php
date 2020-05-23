<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DescriptionCSRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class DescriptionCSRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Description cs');
    }

    protected function getModelAccessKey(): string {
        return 'description_cs';
    }
}
