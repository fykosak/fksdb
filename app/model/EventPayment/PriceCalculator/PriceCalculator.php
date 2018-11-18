<?php

namespace FKSDB\EventPayment\PriceCalculator;

use FKSDB\EventPayment\PriceCalculator\PreProcess\AbstractPreProcess;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;

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

    public function __construct(ModelEvent $event, $currency) {
        $this->event = $event;
        $this->currency = $currency;
    }

    public function addPreProcess(AbstractPreProcess $preProcess) {
        $this->preProcess[] = $preProcess;
    }

    public function execute(array $data, ModelEventPayment $modelEventPayment) {
        $price = new Price(0, $this->currency);
        foreach ($this->preProcess as $preProcess) {
            $preProcess->calculate($data, $this->event);
            $price->add($preProcess->getPrice());
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
