<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;

abstract class AbstractPreProcess {
    /**
     * @var Price
     */
    protected $price;

    public function __construct($currency) {
        $this->price = new Price(0, $currency);
    }

    abstract public function calculate(array $data, ModelEvent $event);

    abstract public function getGridItems(array $data, ModelEvent $event): array;

    public function getPrice(): Price {
        return $this->price;
    }

    protected function getData(array $data) {
        return null;
    }

}
