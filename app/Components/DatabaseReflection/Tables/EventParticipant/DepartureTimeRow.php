<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;
/**
 * Class DepartureTimeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class DepartureTimeRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Departure time');
    }
}
