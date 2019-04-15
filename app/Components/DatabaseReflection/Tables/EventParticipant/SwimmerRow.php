<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use FKSDB\ORM\AbstractModelSingle;
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
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new BinaryPrinter)($model->{$fieldName});
    }
}
