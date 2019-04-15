<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class UsedDrugsRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class UsedDrugsRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Used drugs');
    }
}
