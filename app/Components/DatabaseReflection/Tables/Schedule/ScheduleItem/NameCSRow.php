<?php


namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NameCSRow
 * *
 */
class NameCSRow extends AbstractScheduleItemRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Name cs');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'name_cs';
    }
}
