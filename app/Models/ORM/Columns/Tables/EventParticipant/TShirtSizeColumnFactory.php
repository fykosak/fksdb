<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class TShirtSizeColumnFactory extends ColumnFactory
{

    public const SIZE_MAP = [
        'XS',
        'S',
        'M',
        'L',
        'XL',
        'XXL',
        'XXXL',
    ];
    public const GENDER_MAP = [
        'M' => 'male',
        'F' => 'female',
    ];

    /**
     * @param AbstractModel|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new StringPrinter())($model->tshirt_size);
    }
}
