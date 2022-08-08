<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

abstract class AbstractSymbolGenerator implements TransitionCallback
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    abstract protected function create(PaymentModel $modelPayment, ...$args): array;

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(ModelHolder $holder, ...$args): void
    {
        /** @var PaymentModel $model */
        $model = $holder->getModel();
        $info = $this->create($model, ...$args);
        $this->paymentService->updateModel($model, $info);
    }
}
