<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class NoteRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class NoteRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Note');
    }
}
