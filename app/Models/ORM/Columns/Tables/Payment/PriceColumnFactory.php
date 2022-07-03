<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Utils\Html;

class PriceColumnFactory extends ColumnFactory
{
    /**
     * @param ModelPayment $model
     * @throws \Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return (new PricePrinter())($model->getPrice());
    }
}
