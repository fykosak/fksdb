<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;
/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class CreatedRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }
}
