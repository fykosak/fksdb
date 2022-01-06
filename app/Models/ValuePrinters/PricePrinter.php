<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\Price;
use Nette\Utils\Html;

class PricePrinter extends AbstractValuePrinter
{
    /**
     * @param Price $value
     * @throws UnsupportedCurrencyException
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addText($value->__toString());
    }
}
