<?php

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\Price;

interface Preprocess
{

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
