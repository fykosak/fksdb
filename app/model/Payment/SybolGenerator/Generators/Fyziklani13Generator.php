<?php

namespace FKSDB\Payment\SymbolGenerator\Generators;

use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\Price;
use FKSDB\Payment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use Nette\Application\BadRequestException;

class Fyziklani13Generator extends AbstractSymbolGenerator {
    public function __construct(\ServicePayment $servicePayment) {
        parent::__construct($servicePayment);
    }

    public function create(ModelPayment $modelPayment) {

        if ($modelPayment->hasGeneratedSymbols()) {
            throw new AlreadyGeneratedSymbolsException(\sprintf(_('Payment #%s has already generated symbols.'), $modelPayment->getPaymentId()));
        }
        $maxVariableSymbol = $this->servicePayment->where('event_id', $modelPayment->event_id)->max('variable_symbol');
        $variableId = $maxVariableSymbol % 729200;
        $variableNumber = $variableId + 1 + 729200;
        switch ($modelPayment->currency) {
            case Price::CURRENCY_KC:
                return [
                    'variable_symbol' => $variableNumber,
                    'bank_account' => '38330021/0100',
                    'iban' => 'CZ91 0100 0000 0000 3833 0021',
                ];
            case Price::CURRENCY_EUR:
                return [
                    'variable_symbol' => $variableNumber,
                    'iban' => 'CZ93 0100 0000 4373 0978 0297',
                    'swift' => 'KOMBCZPPXXX',
                ];
            default:
                throw new BadRequestException(_('Unsupported currency'), 500);
        }
    }
}
