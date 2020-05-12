<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonIdRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person info');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'person_info';
    }
}
