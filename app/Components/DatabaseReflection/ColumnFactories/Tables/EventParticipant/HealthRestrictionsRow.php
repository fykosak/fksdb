<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class HealthRestrictionsRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to textRow
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
