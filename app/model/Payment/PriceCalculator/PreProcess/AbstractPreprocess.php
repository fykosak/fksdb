<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;

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
     * @param \FKSDB\ORM\Models\ModelPayment $modelPayment
     * @return array
     */
    abstract public static function getGridItems(ModelPayment $modelPayment): array;
}
