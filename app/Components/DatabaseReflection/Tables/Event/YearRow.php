<?php

namespace FKSDB\Components\DatabaseReflection\Event;

/**
 * Class YearRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class YearRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contests year');
    }
}
