<?php

namespace FKSDB\Payment\Transition;

use FKSDB\ORM\ModelPayment;
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

    /**
     * @param AbstractSymbolGenerator $abstractSymbolGenerator
     */
    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator) {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }

    /**
     * @return AbstractSymbolGenerator
     */
    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    /**
     * @param PriceCalculator $priceCalculator
     */
    public function setPriceCalculator(PriceCalculator $priceCalculator) {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * @return PriceCalculator
     */
    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }

    /**
     * @return string
     */
    public function getInitState(): string {
        return ModelPayment::STATE_NEW;
    }
}
