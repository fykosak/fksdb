<?php

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        if ($model->price) {
            return (new PricePrinter())($model->getPrice());
        }
        return NotSetBadge::getHtml();
    }
}
