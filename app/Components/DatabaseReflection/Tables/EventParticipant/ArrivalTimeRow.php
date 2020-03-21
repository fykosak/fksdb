<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ArrivalTimeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class ArrivalTimeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Arrival time');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'arrival_time';
    }
}
