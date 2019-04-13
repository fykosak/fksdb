<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;
/**
 * Class HealthRestrictionsRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class HealthRestrictionsRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Health restrictions');
    }
}
