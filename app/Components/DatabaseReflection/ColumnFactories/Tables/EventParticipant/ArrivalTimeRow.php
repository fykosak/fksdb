<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
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
