<?php

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Utils\Html;

/**
 * Class PricePrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PricePrinter extends AbstractValuePrinter {
    /**
     * @param Price $value
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addText($value->__toString());
    }
}
