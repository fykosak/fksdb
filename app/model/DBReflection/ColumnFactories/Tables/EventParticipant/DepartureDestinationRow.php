<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class DepartureDestinationRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DepartureDestinationRow extends AbstractParticipantRow {

    public function getTitle(): string {
        return _('Departure destination');
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->departure_destination);
    }
}
