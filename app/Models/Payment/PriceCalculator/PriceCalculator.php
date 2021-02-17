<?php

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;

/**
 * Class PriceCalculator
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceCalculator {

    private ServicePayment $servicePayment;
    /** @var Preprocess[] */
    private array $preProcess = [];

    public function __construct(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    public function addPreProcess(Preprocess $preProcess): void {
        $this->preProcess[] = $preProcess;
    }

    final public function __invoke(ModelPayment $modelPayment): void {
        $price = new Price(0, $modelPayment->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($modelPayment);
            $price->add($subPrice);
        }
        $this->servicePayment->updateModel2($modelPayment, ['price' => $price->getAmount(), 'currency' => $price->getCurrency()]);
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
