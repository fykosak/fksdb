<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Utils\Html;

/**
 * Class PricePrinter
 * *
 */
class PricePrinter extends AbstractValuePrinter {
    /**
     * @param Price|null $value
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($value->__toString());
    }
}
