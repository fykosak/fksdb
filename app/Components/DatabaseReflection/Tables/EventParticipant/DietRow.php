<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DietRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class DietRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Diet');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'diet';
    }
}
