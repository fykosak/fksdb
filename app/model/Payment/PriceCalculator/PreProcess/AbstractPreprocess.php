<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\Payment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;

abstract class AbstractPreProcess {

    abstract public function calculate(ModelPayment $modelPayment): Price;

    abstract public function getGridItems(ModelPayment $modelPayment): array;

    protected function getData(ModelPayment $modelPayment) {
        return null;
    }

}
