<?php


namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class DepartureTicketRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DepartureTicketRow extends AbstractParticipantRow {

    public function getTitle(): string {
        return _('Departure ticket');
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        return (new BinaryPrinter())($model->departure_ticket);
    }
}
