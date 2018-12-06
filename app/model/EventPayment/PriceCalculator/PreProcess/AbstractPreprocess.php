<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;

abstract class AbstractPreProcess {
    /**
     * @var Price
     */
    protected $price;

    abstract public function calculate(array $data, ModelEvent $event, $currency): Price;

    abstract public function getGridItems(array $data, ModelEvent $event): array;

    protected function getData(array $data) {
        return null;
    }

}
