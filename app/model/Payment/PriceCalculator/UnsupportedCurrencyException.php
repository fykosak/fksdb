<?php

namespace FKSDB\Payment\PriceCalculator;

use Throwable;

/**
 * Class UnsupportedCurrencyException
 * *
 */
class UnsupportedCurrencyException extends \Exception {
    /**
     * UnsupportedCurrencyException constructor.
     * @param string $currency
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $currency, int $code = 0, Throwable $previous = null) {
        parent::__construct(\sprintf(_('Currency %s in not supported'), $currency), $code, $previous);
    }
}
