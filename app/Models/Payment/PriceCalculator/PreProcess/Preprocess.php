<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\PaymentModel;
use Fykosak\Utils\Price\MultiCurrencyPrice;

interface Preprocess
{
    public static function calculate(PaymentModel $modelPayment): MultiCurrencyPrice;

    public static function getGridItems(PaymentModel $modelPayment): array;
}
