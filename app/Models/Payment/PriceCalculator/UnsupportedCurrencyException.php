<?php

namespace FKSDB\Models\Payment\PriceCalculator;

class UnsupportedCurrencyException extends \Exception
{

    public function __construct(string $currency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf(_('Currency %s in not supported'), $currency), $code, $previous);
    }
}
