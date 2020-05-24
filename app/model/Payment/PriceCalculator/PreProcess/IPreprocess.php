<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;

/**
 * Class AbstractPreProcess
 * *
 */
interface IPreprocess {

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
