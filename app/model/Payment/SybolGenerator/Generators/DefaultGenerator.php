<?php

namespace FKSDB\Payment\SymbolGenerator\Generators\Generators;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use Nette\OutOfRangeException;

/**
 * Class Fyziklani13Generator
 * @package FKSDB\Payment\SymbolGenerator\Generators
 */
class DefaultGenerator extends AbstractSymbolGenerator {
    /**
     * @var int
     */
    private $variableSymbolStart;
    /**
     * @var int
     */
    private $variableSymbolEnd;
    /**
     * @var array
     */
    private $info;

    /**
     * @param int $variableSymbolStart
     * @param int $variableSymbolEnd
     * @param array $info
     */
    public function setUp(int $variableSymbolStart, int $variableSymbolEnd, array $info) {
        $this->variableSymbolEnd = $variableSymbolEnd;
        $this->variableSymbolStart = $variableSymbolStart;
        $this->info = $info;
    }

    /**
     * @return int
     */
    protected function getVariableSymbolStart(): int {
        return $this->variableSymbolStart;
    }

    /**
     * @return int
     */
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
        throw new UnsupportedCurrencyException($modelPayment->currency, 501);
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    protected function create(ModelPayment $modelPayment) {

        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(\sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId()));
        }
        $maxVariableSymbol = $this->servicePayment->where('event_id', $modelPayment->event_id)
            ->where('variable_symbol>=?', $this->getVariableSymbolStart())
            ->where('variable_symbol<=?', $this->getVariableSymbolEnd())
            ->max('variable_symbol');

        $variableNumber = $maxVariableSymbol + 1;
        if ($variableNumber > $this->getVariableSymbolEnd()) {
            throw new OutOfRangeException(_('variable_symbol overflow'));
        }
        return $this->createPaymentInfo($modelPayment, $variableNumber);
    }
}
