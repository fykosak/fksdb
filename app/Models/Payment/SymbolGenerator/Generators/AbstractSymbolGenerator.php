<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<void>
 */
abstract class AbstractSymbolGenerator implements Statement
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
     * @param ...$args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     * @throws BadTypeException
     */
    final public function __invoke(...$args): void
    {
        [$holder] = $args;
        if (!$holder instanceof PaymentHolder) {
            throw new BadTypeException(PaymentHolder::class, $holder);
        }
        $model = $holder->getModel();
        $info = $this->create($model);
        $this->paymentService->storeModel($info, $model);
    }
}
