<?php

namespace FKSDB\Payment\PriceCalculator;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\PreProcess\IPreprocess;
use FKSDB\Transitions\Callbacks\ITransitionCallback;
use FKSDB\Transitions\IStateModel;

/**
 * Class PriceCalculator
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceCalculator implements ITransitionCallback {

    private ServicePayment $servicePayment;
    /** @var IPreprocess[] */
    private array $preProcess = [];

    public function __construct(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    public function addPreProcess(IPreprocess $preProcess): void {
        $this->preProcess[] = $preProcess;
    }

    final public function __invoke(IStateModel $modelPayment, ...$args): void {
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

    public function invoke(IStateModel $model, ...$args): void {
        $this->__invoke($model, ...$args);
    }
}
