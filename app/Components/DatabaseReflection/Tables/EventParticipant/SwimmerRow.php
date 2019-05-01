<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class SwimmerRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class SwimmerRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Swimmer');
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @param string $fieldNam
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldNam): Html {
        return (new BinaryPrinter)($model->swimmer);
    }
}
