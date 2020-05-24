<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DietRow
 * *
 */
class DietRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Diet');
    }

    protected function getModelAccessKey(): string {
        return 'diet';
    }
}
