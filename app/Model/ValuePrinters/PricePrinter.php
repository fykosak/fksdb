<?php

namespace FKSDB\Model\ValuePrinters;

use FKSDB\Model\Payment\Price;
use FKSDB\Model\Payment\PriceCalculator\UnsupportedCurrencyException;
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
