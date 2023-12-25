<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\Http\IResponse;

/**
 * @phpstan-type TInfo array<string,array{
 *      bank_account?: string,
 *      bank_name:string,
 *      recipient:string,
 *      iban:string,
 *      constant_symbol?:string,
 *      swift?: string,
 * }>
 * @implements Statement<void,PaymentHolder>
 */
class DefaultGenerator implements Statement
{

    protected PaymentService $paymentService;

    /** @phpstan-var TInfo $params */
    private array $params;

    /**
     * @phpstan-param TInfo $params
     */
    public function __construct(array $params, PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->params = $params;
    }

    /**
     * @param PaymentHolder $args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     * @throws BadTypeException
     * @throws \Exception
     */
    final public function __invoke(...$args): void
    {
        [$holder] = $args;
        if (!$holder instanceof PaymentHolder) {
            throw new BadTypeException(PaymentHolder::class, $holder);
        }
        $model = $holder->getModel();
        if ($model->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(
                \sprintf(_('Payment #%s has already generated symbols.'), $model->payment_id)
            );
        }
        /** @var SchedulePaymentModel|null $schedule */
        $schedule = $model->getSchedulePayment()->fetch();
        $contains = 0;
        if ($schedule) {
            $contains += 1;
        } else {
            throw new NotImplementedException();
        }
        $variableNumber = $this->createVariableSymbol($contains, $model);
        if (!isset($this->params[$model->getCurrency()->value])) {
            throw new UnsupportedCurrencyException($model->getCurrency(), IResponse::S501_NOT_IMPLEMENTED);
        }
        $info = $this->params[$model->getCurrency()->value];
        $info['variable_symbol'] = $variableNumber;

        $this->paymentService->storeModel($info, $model);
    }

    private function createVariableSymbol(int $contains, PaymentModel $payment): string
    {
        $date = new \DateTime();
        return sprintf(
            '%02d%02d%01d%05d',
            $date->format('y'),
            $date->format('m'),
            $contains,
            $payment->payment_id
        );
    }
}
