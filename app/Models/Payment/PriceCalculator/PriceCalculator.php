<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\Payment\Transition\PaymentHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;
use Fykosak\Utils\Price\Price;

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
     * @param PaymentHolder $holder
     * @throws \Exception
     */
    final public function __invoke(ModelHolder $holder, ...$args): void
    {
        $price = new Price($holder->getModel()->getCurrency(), 0);
        foreach ($this->preProcess as $preProcess) {
            $subPrice = $preProcess->calculate($holder->getModel());
            $price->add($subPrice);
        }
        $this->servicePayment->updateModel(
            $holder->getModel(),
            ['price' => $price->getAmount(), 'currency' => $price->getCurrency()->value]
        );
    }

    /**
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

    /**
     * @throws \Exception
     */
    public function invoke(ModelHolder $holder, ...$args): void
    {
        $this->__invoke($holder, ...$args);
    }
}
