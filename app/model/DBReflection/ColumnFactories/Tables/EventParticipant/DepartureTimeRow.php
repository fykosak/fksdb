<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class DepartureTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DepartureTimeRow extends AbstractParticipantRow {

    public function getTitle(): string {
        return _('Departure time');
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->departure_time);
    }
}
