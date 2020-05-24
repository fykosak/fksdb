<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DepartureDestinationRow
 * *
 */
class DepartureDestinationRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Departure destination');
    }

    protected function getModelAccessKey(): string {
        return 'departure_destination';
    }
}
