<?php

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->price) {
            return (new PricePrinter())($model->getPrice());
        }
        return NotSetBadge::getHtml();
    }
}
