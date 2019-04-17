<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;
/**
 * Class EvenParticipantIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class EvenParticipantIdRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event participant Id');
    }
}
