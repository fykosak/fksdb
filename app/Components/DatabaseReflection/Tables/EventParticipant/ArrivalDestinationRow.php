<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ArrivalDestinationRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class ArrivalDestinationRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Arrival destination');
    }

    protected function getModelAccessKey(): string {
        return 'arrival_destination';
    }
}
