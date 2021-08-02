<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class PriceCalculator implements TransitionCallback
{

    private ServicePayment $servicePayment;
    /** @var Preprocess[] */
    private array $preProcess = [];

    public function __construct(ServicePayment $servicePayment)
    {
        $this->servicePayment = $servicePayment;
    }

    public function addPreProcess(Preprocess $preProcess): void
    {
        $this->preProcess[] = $preProcess;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array[]
     */
    public function getGridItems(ModelPayment $modelPayment): array
    {
        $items = [];
        foreach ($this->preProcess as $preProcess) {
            $items = \array_merge($items, $preProcess->getGridItems($modelPayment));
        }
        return $items;
    }

    public function invoke(ModelHolder $holder, ...$args): void
    {
        $this->__invoke($holder, ...$args);
    }

    final public function __invoke(ModelHolder $holder, ...$args): void
    {
        $price = new Price(0, $holder->getModel()->currency);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($holder->getModel());
            $price->add($subPrice);
        }
        $this->servicePayment->updateModel(
            $holder->getModel(),
            ['price' => $price->getAmount(), 'currency' => $price->getCurrency()]
        );
    }
}
