<?php


namespace FKSDB\EventPayment\Transition;


use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;

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
