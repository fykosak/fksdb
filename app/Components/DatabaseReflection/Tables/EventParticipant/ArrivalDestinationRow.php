<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ArrivalDestinationRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class ArrivalDestinationRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Arrival destination');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'arrival_destination';
    }
}
