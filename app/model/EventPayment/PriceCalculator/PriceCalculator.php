<?php

namespace FKSDB\EventPayment\PriceCalculator;

use FKSDB\EventPayment\PriceCalculator\PreProcess\AbstractPreProcess;
use FKSDB\ORM\ModelEvent;

class PriceCalculator {
    /**
     * @var AbstractPreProcess[]
     */
    private $preProcess = [];
    /**
     * @var ModelEvent
     */
    private $event;

    public function __construct(ModelEvent $event) {
        $this->event = $event;
    }

    public function addPreProcess(AbstractPreProcess $preProcess) {
        $this->preProcess[] = $preProcess;
    }

    public function execute(array $data) {
        $price = ['kc' => 0, 'eur' => 0];
        foreach ($this->preProcess as $preProcess) {
            $preProcess->calculate($data, $this->event);
            $price['kc'] += $preProcess->getPrice()['kc'];
            $price['eur'] += $preProcess->getPrice()['eur'];
        }
        return $price;
    }

    public function getGridItems(array $data) {
        $items = [];
        foreach ($this->preProcess as $preProcess) {
            $items = \array_merge($items, $preProcess->getGridItems($data, $this->event));
        }
        return $items;
    }

}
