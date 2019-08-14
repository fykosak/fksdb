<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Utils\Html;
use function is_null;

/**
 * Class PricePrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class PricePrinter extends AbstractValuePrinter {
    /**
     * @param Price|null $value
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function getHtml($value): Html {
        if (is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($value->__toString());
    }
}
