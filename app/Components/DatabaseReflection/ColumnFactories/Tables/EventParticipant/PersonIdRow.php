<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class PersonIdRow
 * *
 */
class PersonIdRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Person info');
    }

    protected function getModelAccessKey(): string {
        return 'person_info';
    }
}
