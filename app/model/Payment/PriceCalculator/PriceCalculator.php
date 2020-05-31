<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\PreProcess\IPreprocess;

/**
 * Class PriceCalculator
 * *
 */
class PriceCalculator {
    /**
     * @var IPreprocess[]
     */
    private array $preProcess = [];

    public function addPreProcess(IPreprocess $preProcess): void {
        $this->preProcess[] = $preProcess;
    }

    final public function __invoke(ModelPayment $modelPayment): void {
        $price = new Price(0, $modelPayment->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($modelPayment);
            $price->add($subPrice);
        }
        // TODO to service
        $modelPayment->update(['price' => $price->getAmount(), 'currency' => $price->getCurrency()]);
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
}
