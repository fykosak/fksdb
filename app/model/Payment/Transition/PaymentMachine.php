<?php

namespace FKSDB\Payment\Transition;

use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Payment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\Transitions\Machine;

class PaymentMachine extends Machine {
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;
    /**
     * @var AbstractSymbolGenerator
     */
    private $symbolGenerator;

    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator) {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }

    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    public function setPriceCalculator(PriceCalculator $priceCalculator) {
        $this->priceCalculator = $priceCalculator;
    }

    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }
}
