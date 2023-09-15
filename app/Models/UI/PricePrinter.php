<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Utils\Html;

class PricePrinter
{
    /**
     * @param MultiCurrencyPrice|Price|null|mixed $price
     * @throws NotImplementedException
     * @throws UnsupportedCurrencyException
     */
    public static function getHtml($price): Html
    {
        if (\is_null($price)) {
            return NotSetBadge::getHtml();
        }
        if ($price instanceof Price || $price instanceof MultiCurrencyPrice) {
            return Html::el('span')->addText($price->__toString());
        }
        throw new NotImplementedException();
    }
}
