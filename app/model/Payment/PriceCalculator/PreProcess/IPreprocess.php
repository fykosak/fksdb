<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;

/**
 * Class AbstractPreProcess
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
interface IPreprocess {

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
