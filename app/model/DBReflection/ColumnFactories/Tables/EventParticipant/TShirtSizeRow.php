<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class TShirtSizeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TShirtSizeRow extends DefaultColumnFactory {

    public const SIZE_MAP = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
    ];
    public const GENDER_MAP = [
        'M' => 'male',
        'F' => 'female',
    ];

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->tshirt_size);
    }
}
