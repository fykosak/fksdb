<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Utils\Html;

class PricePrinter extends AbstractValuePrinter
{
    /**
     * @param Price|MultiCurrencyPrice $value
     * @throws UnsupportedCurrencyException
     * @throws NotImplementedException
     */
    protected function getHtml($value): Html
    {
        if ($value instanceof Price || $value instanceof MultiCurrencyPrice) {
            return Html::el('span')->addText($value->__toString());
        }
        throw new NotImplementedException();
    }
}
