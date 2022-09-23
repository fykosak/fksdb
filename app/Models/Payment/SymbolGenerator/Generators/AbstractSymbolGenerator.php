<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\Models\Transitions\Holder\PaymentHolder;

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
    abstract protected function create(PaymentModel $modelPayment): array;

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     * @throws BadTypeException
     */
    final public function __invoke(ModelHolder $holder, ...$args): void
    {
        if (!$holder instanceof PaymentHolder) {
            throw new BadTypeException(PaymentHolder::class, $holder);
        }
        $model = $holder->getModel();
        $info = $this->create($model);
        $this->paymentService->storeModel($info, $model);
    }
}
