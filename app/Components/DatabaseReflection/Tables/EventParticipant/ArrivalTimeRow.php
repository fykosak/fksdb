<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ArrivalTimeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class ArrivalTimeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Arrival time');
    }

    protected function getModelAccessKey(): string {
        return 'arrival_time';
    }
}
