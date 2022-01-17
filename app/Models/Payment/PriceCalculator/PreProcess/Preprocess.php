<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\ModelPayment;
use Fykosak\Utils\Price\MultiCurrencyPrice;

interface Preprocess
{
    public static function calculate(ModelPayment $modelPayment): MultiCurrencyPrice;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
