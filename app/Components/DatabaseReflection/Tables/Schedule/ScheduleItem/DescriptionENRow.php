<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DescriptionCSRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class DescriptionENRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Description en');
    }

    protected function getModelAccessKey(): string {
        return 'description_en';
    }
}
