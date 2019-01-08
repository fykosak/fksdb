<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;

abstract class AbstractPreProcess {

    abstract public function calculate(ModelPayment $modelPayment): Price;

    abstract public function getGridItems(ModelPayment $modelPayment): array;

    protected function getData(ModelPayment $modelPayment) {
        return null;
    }

}
