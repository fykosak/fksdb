<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PricePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class PriceRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Price');
    }

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->price) {
            return (new PricePrinter)($model->getPrice());
        }
        return NotSetBadge::getHtml();
    }
}
