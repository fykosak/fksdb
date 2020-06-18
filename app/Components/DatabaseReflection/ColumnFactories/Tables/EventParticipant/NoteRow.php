<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NoteRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to textRow
 */
class NoteRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Note');
    }

    protected function getModelAccessKey(): string {
        return 'note';
    }
}
