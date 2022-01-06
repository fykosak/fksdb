<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use Fykosak\Utils\Price\Currency;
use Nette\InvalidStateException;

class UnsupportedCurrencyException extends InvalidStateException
{
    public function __construct(Currency $currency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf(_('Currency %s in not supported'), $currency->value), $code, $previous);
    }
}
