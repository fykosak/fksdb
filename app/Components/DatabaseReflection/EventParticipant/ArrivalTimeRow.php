<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class ArrivalTimeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class ArrivalTimeRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Arrival time');
    }
}
