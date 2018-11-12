<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEvent;

abstract class AbstractPreProcess {
    protected $price = [
        'kc' => 0,
        'eur' => 0,
    ];

    abstract public function calculate(array $data, ModelEvent $event);

    abstract public function getGridItems(array $data, ModelEvent $event): array;

    public function getPrice() {
        return $this->price;
    }

    protected function getData(array $data) {
        return null;
    }

}
