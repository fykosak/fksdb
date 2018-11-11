<?php

namespace FKSDB\EventPayment\SymbolGenerator\Generators;

use FKSDB\ORM\ModelEventPayment;
use http\Exception\BadMethodCallException;

class Fyziklani13Generator extends \FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator {
    public function __construct(\ServiceEventPayment $serviceEventPayment) {
        parent::__construct($serviceEventPayment);
    }

    public function crate(ModelEventPayment $modelEventPayment) {

        if ($modelEventPayment->constant_symbol || $modelEventPayment->variable_symbol || $modelEventPayment->specific_symbol) {
            throw new BadMethodCallException('Platba má už vygenerované symboly');
        }
        $maxVariableSymbol = $this->serviceEventPayment->where('event_id', $modelEventPayment->event_id)->max('variable_symbol');
        $variableId = $maxVariableSymbol % 7292000;
        $variableNumber = $variableId + 1 + 7292000;

        return [
            'constant_symbol' => 1234,
            'variable_symbol' => $variableNumber,
            'specific_symbol' => 1234,
        ];
    }
}
