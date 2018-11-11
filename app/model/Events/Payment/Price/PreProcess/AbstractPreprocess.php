<?php

namespace FKSDB\Models\Payment\Price\PreProcess;

use FKSDB\ORM\ModelEvent;

abstract class AbstractPreProcess {
    protected $price = [
        'kc' => 0,
        'eur' => 0,
    ];

    abstract function run(array $data, ModelEvent $event);

    public function getPrice() {
        return $this->price;
    }

}
