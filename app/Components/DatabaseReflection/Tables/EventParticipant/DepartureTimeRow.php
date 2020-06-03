<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class DepartureTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DepartureTimeRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Departure time');
    }

    protected function getModelAccessKey(): string {
        return 'departure_time';
    }
}
