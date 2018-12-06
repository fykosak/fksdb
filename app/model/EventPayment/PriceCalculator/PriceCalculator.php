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
        if ($this->currency == null) {
            throw new \InvalidArgumentException('Currency is not set');
        }
        $price = new Price(0, $this->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($data, $this->event);
            $price->add($subPrice);
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

    public function getCurrencies() {
        return Price::getAllCurrencies();
    }

}
