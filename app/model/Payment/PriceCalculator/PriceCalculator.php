<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\PreProcess\IPreprocess;

/**
 * Class PriceCalculator
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceCalculator {
    /** @var ServicePayment */
    private $servicePayment;
    /** @var IPreprocess[] */
    private $preProcess = [];

    /**
     * PriceCalculator constructor.
     * @param ServicePayment $servicePayment
     */
    public function __construct(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param IPreprocess $preProcess
     * @return void
     */
    public function addPreProcess(IPreprocess $preProcess) {
        $this->preProcess[] = $preProcess;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return void
     */
    final public function __invoke(ModelPayment $modelPayment) {
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
