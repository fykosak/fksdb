<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ApplicationStateTrait;

/**
 * Class StatusRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class StatusRow extends AbstractParticipantRow {
    use ApplicationStateTrait;

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'status';
    }
}
