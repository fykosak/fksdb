<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\PreProcess\AbstractPreProcess;

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

    /**
     * @param $currency
     */
    public function setCurrency(string $currency) {
        $this->currency = $currency;
    }

    /**
     * @param AbstractPreProcess $preProcess
     */
    public function addPreProcess(AbstractPreProcess $preProcess) {
        $this->preProcess[] = $preProcess;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return Price
     */
    public function execute(ModelPayment $modelPayment): Price {
        $price = new Price(0, $this->getCurrency());
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($modelPayment);
            $price->add($subPrice);
        }
        return $price;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array[]
     */
    public function getGridItems(ModelPayment $modelPayment): array {
        $items = [];
        foreach ($this->preProcess as $preProcess) {
            $items = \array_merge($items, $preProcess->getGridItems($modelPayment));
        }
        return $items;
    }

    /**
     * @return string
     */
    private function getCurrency(): string {
        if ($this->currency == null) {
            throw new \InvalidArgumentException('Currency is not set');
        }
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getCurrencies(): array {
        return Price::getAllCurrencies();
    }

}
