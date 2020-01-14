<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NameEnRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup
 */
class NameEnRow extends AbstractScheduleGroupRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Name en');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'name_en';
    }
}
