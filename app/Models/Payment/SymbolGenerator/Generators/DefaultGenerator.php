<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use Nette\Http\IResponse;
use Nette\OutOfRangeException;

class DefaultGenerator extends AbstractSymbolGenerator
{
    private int $variableSymbolStart;

    private int $variableSymbolEnd;

    private array $info;

    public function setUp(int $variableSymbolStart, int $variableSymbolEnd, array $info): void
    {
        $this->variableSymbolEnd = $variableSymbolEnd;
        $this->variableSymbolStart = $variableSymbolStart;
        $this->info = $info;
    }

    protected function getVariableSymbolStart(): int
    {
        return $this->variableSymbolStart;
    }

    protected function getVariableSymbolEnd(): int
    {
        return $this->variableSymbolEnd;
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws \Exception
     */
    protected function createPaymentInfo(PaymentModel $modelPayment, int $variableNumber): array
    {
        if (array_key_exists($modelPayment->getCurrency()->value, $this->info)) {
            $info = $this->info[$modelPayment->getCurrency()->value];
            $info['variable_symbol'] = $variableNumber;
            return $info;
        }
        throw new UnsupportedCurrencyException($modelPayment->getCurrency(), IResponse::S501_NOT_IMPLEMENTED);
    }

    /**
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     * @throws \Exception
     */
    protected function create(PaymentModel $modelPayment): array
    {
        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(
                \sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId())
            );
        }
        $maxVariableSymbol = $modelPayment->event->getPayments()
            ->where('variable_symbol>=?', $this->getVariableSymbolStart())
            ->where('variable_symbol<=?', $this->getVariableSymbolEnd())
            ->max('variable_symbol');

        $variableNumber = ($maxVariableSymbol == 0) ? $this->getVariableSymbolStart() : ($maxVariableSymbol + 1);
        if ($variableNumber > $this->getVariableSymbolEnd()) {
            throw new OutOfRangeException(_('variable_symbol overflow'));
        }
        return $this->createPaymentInfo($modelPayment, $variableNumber);
    }
}
