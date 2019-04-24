<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

/**
 * Class TShirtSizeRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class TShirtSizeRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
       return _('T-shirt size');
    }
}
