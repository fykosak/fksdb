<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NameCsRow
 * *
 */
class NameCsRow extends AbstractScheduleGroupRow {
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
