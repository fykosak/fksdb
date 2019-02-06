<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;

/**
 * Class AbstractPreProcess
 * @package FKSDB\Payment\PriceCalculator\PreProcess
 */
abstract class AbstractPreProcess {

    /**
     * @param ModelPayment $modelPayment
     * @return Price
     */
    abstract public static function calculate(ModelPayment $modelPayment): Price;

    /**
     * @param ModelPayment $modelPayment
     * @return array
     */
    abstract static public function getGridItems(ModelPayment $modelPayment): array;
}
