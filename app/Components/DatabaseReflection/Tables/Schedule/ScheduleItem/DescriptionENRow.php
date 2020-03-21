<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DescriptionCSRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem
 */
class DescriptionENRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Description en');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'description_en';
    }
}