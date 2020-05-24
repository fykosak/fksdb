<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;

/**
 * Class AbstractPreProcess
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
interface IPreprocess {

    /**
     * @param ModelPayment $modelPayment
     * @return Price
     */
    public static function calculate(ModelPayment $modelPayment): Price;

    /**
     * @param ModelPayment $modelPayment
     * @return array
     */
    public static function getGridItems(ModelPayment $modelPayment): array;
}
