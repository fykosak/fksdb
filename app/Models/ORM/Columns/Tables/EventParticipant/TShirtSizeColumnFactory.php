<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\EventParticipant;

use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class TShirtSizeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TShirtSizeColumnFactory extends DefaultColumnFactory {

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
