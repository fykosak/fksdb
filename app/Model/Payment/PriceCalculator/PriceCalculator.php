<?php

namespace FKSDB\Model\Payment\PriceCalculator;

use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\ServicePayment;
use FKSDB\Model\Payment\Price;
use FKSDB\Model\Payment\PriceCalculator\PreProcess\IPreprocess;
use FKSDB\Model\Transitions\IStateModel;
use FKSDB\model\Transitions\Transition\Callbacks\ITransitionCallback;

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

    final public function __invoke(IStateModel $model, ...$args): void {
        $price = new Price(0, $model->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($model);
            $price->add($subPrice);
        }
        $this->servicePayment->updateModel2($model, ['price' => $price->getAmount(), 'currency' => $price->getCurrency()]);
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
