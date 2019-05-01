<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class CreatedRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class CreatedRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Created');
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @param string $fieldNam
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldNam): Html {
        return (new DatePrinter('c'))($model->created);
    }
}
