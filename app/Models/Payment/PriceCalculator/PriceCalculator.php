<?php

namespace FKSDB\Models\Payment\PriceCalculator;


use FKSDB\Models\Transitions\Holder\IModelHolder;
use FKSDB\Models\Transitions\Callbacks\ITransitionCallback;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\IPreprocess;

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

    final public function __invoke(IModelHolder $model, ...$args): void {
        $price = new Price(0, $model->getModel()->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($model->getModel());
            $price->add($subPrice);
        }
        $this->servicePayment->updateModel2($model->getModel(), ['price' => $price->getAmount(), 'currency' => $price->getCurrency()]);
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

    public function invoke(IModelHolder $model, ...$args): void {
        $this->__invoke($model, ...$args);
    }
}
