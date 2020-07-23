<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ValuePrinters\StringPrinter;
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

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->arrival_destination);
    }
}
