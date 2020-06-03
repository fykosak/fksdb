<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class UsedDrugsRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to textRow
 */
class UsedDrugsRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Used drugs');
    }

    protected function getModelAccessKey(): string {
       return 'used_drugs';
    }
}
