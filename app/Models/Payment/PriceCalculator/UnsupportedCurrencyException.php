<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use Nette\InvalidStateException;

class UnsupportedCurrencyException extends InvalidStateException
{
    public function __construct(string $currency, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf(_('Currency %s in not supported'), $currency), $code, $previous);
    }
}
