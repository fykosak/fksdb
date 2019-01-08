<?php

namespace FKSDB\Payment\SymbolGenerator\Generators;

use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

class Fyziklani13Generator extends AbstractSymbolGenerator {
    public function __construct(\ServicePayment $servicePayment) {
        parent::__construct($servicePayment);
    }

    public function create(ModelPayment $modelPayment) {

        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(\sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId()));
        }
        $maxVariableSymbol = $this->servicePayment->where('event_id', $modelPayment->event_id)->max('variable_symbol');
        $variableId = $maxVariableSymbol % 7292000;
        $variableNumber = $variableId + 1 + 7292000;

        return [
            'constant_symbol' => 1234,
            'variable_symbol' => $variableNumber,
            'specific_symbol' => 1234,
            'bank_account' => '123456789/1234',
        ];
    }
}
