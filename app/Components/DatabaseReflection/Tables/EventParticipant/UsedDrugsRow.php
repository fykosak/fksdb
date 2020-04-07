<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class UsedDrugsRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class UsedDrugsRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Used drugs');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
       return 'used_drugs';
    }
}
