<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class ArrivalDestinationRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ArrivalDestinationRow extends AbstractParticipantRow {

    public function getTitle(): string {
        return _('Arrival destination');
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->arrival_destination);
    }
}
