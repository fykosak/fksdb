<?php

namespace FKSDB\Payment\Transition;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Payment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\Transitions\Machine;
use Nette\Database\Connection;

/**
 * Class PaymentMachine
 * @package FKSDB\Payment\Transition
 */
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
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;

    /**
     * PaymentMachine constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param PriceCalculator $priceCalculator
     * @param AbstractSymbolGenerator $abstractSymbolGenerator
     * @param Connection $connection
     * @param ServicePayment $servicePayment
     */
    public function __construct(ModelEvent $event, PriceCalculator $priceCalculator, AbstractSymbolGenerator $abstractSymbolGenerator, Connection $connection, ServicePayment $servicePayment) {
        parent::__construct($connection, $servicePayment);
        $this->priceCalculator = $priceCalculator;
        $this->symbolGenerator = $abstractSymbolGenerator;
        $this->event = $event;
    }

    /**
     * @return AbstractSymbolGenerator
     */
    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    /**
     * @return PriceCalculator
     */
    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }

    /**
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    public function getEvent(): ModelEvent {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getCreatingState(): string {
        return ModelPayment::STATE_NEW;
    }
}
