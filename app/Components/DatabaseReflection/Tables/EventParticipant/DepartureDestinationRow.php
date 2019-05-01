<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DepartureDestinationRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class DepartureDestinationRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Departure destination');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'departure_destination';
    }
}
