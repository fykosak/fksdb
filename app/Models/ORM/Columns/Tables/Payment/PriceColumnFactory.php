<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\UI\NotSetBadge;
use FKSDB\Models\UI\PricePrinter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PaymentModel|ItemModel,never>
 */
class PriceColumnFactory extends AbstractColumnFactory
{
    /**
     * @param PaymentModel $model
     * @throws \Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return PricePrinter::getHtml($model->getPrice());
    }
}
