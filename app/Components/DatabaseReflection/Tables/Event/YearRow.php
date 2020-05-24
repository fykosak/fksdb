<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class YearRow
 * *
 */
class YearRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Contests year');
    }

    protected function getModelAccessKey(): string {
        return 'year';
    }
}
