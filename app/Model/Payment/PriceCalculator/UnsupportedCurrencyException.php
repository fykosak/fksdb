<?php

namespace FKSDB\Model\Payment\PriceCalculator;

use Throwable;

/**
 * Class UnsupportedCurrencyException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UnsupportedCurrencyException extends \Exception {

    public function __construct(string $currency, int $code = 0, ?Throwable $previous = null) {
        parent::__construct(\sprintf(_('Currency %s in not supported'), $currency), $code, $previous);
    }
}