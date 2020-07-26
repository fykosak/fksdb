<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class ArrivalTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ArrivalTimeRow extends AbstractParticipantRow {

    public function getTitle(): string {
        return _('Arrival time');
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->arrival_time);
    }
}
