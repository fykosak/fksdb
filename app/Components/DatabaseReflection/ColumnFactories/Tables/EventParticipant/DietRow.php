<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DietRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to textRow
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
