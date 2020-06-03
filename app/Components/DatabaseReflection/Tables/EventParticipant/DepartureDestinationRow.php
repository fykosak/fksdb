<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DepartureDestinationRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DepartureDestinationRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Departure destination');
    }

    protected function getModelAccessKey(): string {
        return 'departure_destination';
    }
}
