<?php

namespace FKSDB\EventPayment\SymbolGenerator\Generators;

use FKSDB\EventPayment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\ORM\ModelPayment;

class Fyziklani13Generator extends \FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator {
    public function __construct(\ServicePayment $serviceEventPayment) {
        parent::__construct($serviceEventPayment);
    }

    public function create(ModelPayment $modelPayment) {

        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(\sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId()));
        }
        $maxVariableSymbol = $this->serviceEventPayment->where('event_id', $modelPayment->event_id)->max('variable_symbol');
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
