<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class HealthRestrictionsRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class HealthRestrictionsRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Health restrictions');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'health_restrictions';
    }
}
