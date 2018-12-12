<?php

namespace FKSDB\EventPayment\PriceCalculator;

use FKSDB\EventPayment\PriceCalculator\PreProcess\AbstractPreProcess;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;

class PriceCalculator {
    /**
     * @var AbstractPreProcess[]
     */
    private $preProcess = [];
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $currency;

    public function __construct(ModelEvent $event) {
        $this->event = $event;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function addPreProcess(AbstractPreProcess $preProcess) {
        $this->preProcess[] = $preProcess;
    }

    public function execute(array $data) {
        $price = new Price(0, $this->getCurrency());
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($data, $this->event, $this->getCurrency());
            $price->add($subPrice);
        }
        return $price;
    }

    public function getGridItems(ModelPayment $modelPayment) {
        $items = [];
        foreach ($this->preProcess as $preProcess) {
            $items = \array_merge($items, $preProcess->getGridItems($modelPayment));
        }
        return $items;
    }

    private function getCurrency() {
        if ($this->currency == null) {
            throw new \InvalidArgumentException('Currency is not set');
        }
        return $this->currency;
    }

    public function getCurrencies() {
        return Price::getAllCurrencies();
    }

}
