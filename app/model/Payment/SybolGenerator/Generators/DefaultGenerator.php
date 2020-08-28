<?php

namespace FKSDB\Payment\SymbolGenerator\Generators\Generators;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use Nette\Http\Response;
use Nette\OutOfRangeException;

/**
 * Class DefaultGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DefaultGenerator extends AbstractSymbolGenerator {

    private int $variableSymbolStart;

    private int $variableSymbolEnd;

    private array $info;

    public function setUp(int $variableSymbolStart, int $variableSymbolEnd, array $info): void {
        $this->variableSymbolEnd = $variableSymbolEnd;
        $this->variableSymbolStart = $variableSymbolStart;
        $this->info = $info;
    }

    protected function getVariableSymbolStart(): int {
        return $this->variableSymbolStart;
    }

    protected function getVariableSymbolEnd(): int {
        return $this->variableSymbolEnd;
    }

    /**
     * @param ModelPayment $modelPayment
     * @param int $variableNumber
     * @return array
     * @throws UnsupportedCurrencyException
     */
    protected function createPaymentInfo(ModelPayment $modelPayment, int $variableNumber): array {
        if (array_key_exists($modelPayment->currency, $this->info)) {
            $info = $this->info[$modelPayment->currency];
            $info['variable_symbol'] = $variableNumber;
            return $info;
        }
        throw new UnsupportedCurrencyException($modelPayment->currency, Response::S501_NOT_IMPLEMENTED);
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    protected function create(ModelPayment $modelPayment): array {

        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(\sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId()));
        }
        $maxVariableSymbol = $this->servicePayment->where('event_id', $modelPayment->event_id)
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
