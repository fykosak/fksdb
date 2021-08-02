<?php

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

class PriceColumnFactory extends ColumnFactory
{
    /**
     * @param AbstractModel|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        if (\is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return (new PricePrinter())($model->getPrice());
    }
}
