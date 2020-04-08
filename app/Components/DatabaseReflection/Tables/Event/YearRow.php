<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class YearRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class YearRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contests year');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'year';
    }
}
