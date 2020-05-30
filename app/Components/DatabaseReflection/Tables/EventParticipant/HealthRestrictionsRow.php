<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class HealthRestrictionsRow
 * *
 */
class HealthRestrictionsRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Health restrictions');
    }

    protected function getModelAccessKey(): string {
        return 'health_restrictions';
    }
}
