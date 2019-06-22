<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class EvenParticipantIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class EvenParticipantIdRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event participant Id');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'event_participant_id';
    }
}
