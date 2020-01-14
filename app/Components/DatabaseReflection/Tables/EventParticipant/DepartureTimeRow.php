<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DepartureTimeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class DepartureTimeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Departure time');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'departure_time';
    }
}
