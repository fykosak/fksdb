<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NoteRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class NoteRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Note');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'note';
    }
}
