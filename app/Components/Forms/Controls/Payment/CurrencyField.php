<?php

namespace FKSDB\Components\Forms\Controls\Payment;

use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Forms\Controls\SelectBox;

/**
 * Class CurrencyField
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CurrencyField extends SelectBox {
    /**
     * CurrencyField constructor.
     * @throws UnsupportedCurrencyException
     */
    public function __construct() {
        parent::__construct(_('Currency'));
        $items = [];
        foreach (Price::getAllCurrencies() as $currency) {
            $items[$currency] = Price::getLabel($currency);
        }
        $this->setItems($items)->setPrompt(_('Select currency'));
    }
}
