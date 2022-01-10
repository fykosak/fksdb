<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

abstract class AbstractSymbolGenerator implements TransitionCallback
{
    protected ServicePayment $servicePayment;

    public function __construct(ServicePayment $servicePayment)
    {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    abstract protected function create(ModelPayment $modelPayment, ...$args): array;

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(ModelHolder $holder, ...$args): void
    {
        /** @var ModelPayment $model */
        $model = $holder->getModel();
        $info = $this->create($model, ...$args);
        $this->servicePayment->updateModel($model, $info);
    }

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function invoke(ModelHolder $holder, ...$args): void
    {
        /** @var ModelPayment $model */
        $model = $holder->getModel();
        $info = $this->create($model, ...$args);
        $this->servicePayment->updateModel($model, $info);
    }
}
