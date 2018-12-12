<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;

abstract class AbstractPreProcess {

    abstract public function calculate(array $data, ModelEvent $event, $currency): Price;

    abstract public function getGridItems(ModelPayment $modelPayment): array;

    protected function getData(array $data) {
        return null;
    }

}
