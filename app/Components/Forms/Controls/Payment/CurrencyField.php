<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Payment;

use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\Currency;
use Nette\Forms\Controls\SelectBox;

class CurrencyField extends SelectBox
{
    /**
     * CurrencyField constructor.
     * @throws UnsupportedCurrencyException
     */
    public function __construct()
    {
        parent::__construct(_('Currency'));
        $items = [];
        foreach (Currency::cases() as $currency) {
            $items[$currency->value] = $currency->getLabel();
        }
        $this->setItems($items)->setPrompt(_('Select currency'));
    }
}
