<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
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
