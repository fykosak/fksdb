<?php

namespace FKSDB\Components\Forms\Factories\EventPayment;

use FKSDB\EventPayment\PriceCalculator\Price;
use Nette\Forms\Controls\SelectBox;

class CurrencyField extends SelectBox {
    public function __construct() {
        parent::__construct(_('Currency'));
        $items = [];
        foreach (Price::getAllCurrencies() as $currency) {
            $items[$currency] = Price::getLabel($currency);
        }
        $this->setItems($items)->setPrompt(_('Select currency'));
    }
}
