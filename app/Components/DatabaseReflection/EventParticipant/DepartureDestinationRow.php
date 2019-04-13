<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class DepartureDestinationRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class DepartureDestinationRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Departure destination');
    }
}
