<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;

abstract class AbstractPreProcess {

    abstract public function calculate(ModelPayment $modelPayment): Price;

    abstract public function getGridItems(ModelPayment $modelPayment): array;

    protected function getData(ModelPayment $modelPayment) {
        return null;
    }

}
