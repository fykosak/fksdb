<?php

namespace FKSDB\Payment\Transition;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\Machine;
use Nette\Database\Context;

/**
 * Class PaymentMachine
 * *
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
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * PaymentMachine constructor.
     * @param Context $connection
     * @param ServicePayment $servicePayment
     * @param ServiceEvent $serviceEvent
     */
    public function __construct(Context $connection, ServicePayment $servicePayment, ServiceEvent $serviceEvent) {
        parent::__construct($connection, $servicePayment);
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param AbstractTransitionsGenerator $factory
     * @return void
     */
    public function setTransitions(AbstractTransitionsGenerator $factory) {
        $factory->createTransitions($this);
    }

    /**
     * @param int $eventId
     * @return void
     */
    public function setEventId(int $eventId) {
        $this->event = $this->serviceEvent->findByPrimary($eventId);
    }

    /**
     * @param AbstractSymbolGenerator $abstractSymbolGenerator
     * @return void
     */
    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator) {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }

    /**
     * @param PriceCalculator $priceCalculator
     * @return void
     */
    public function setPriceCalculator(PriceCalculator $priceCalculator) {
        $this->priceCalculator = $priceCalculator;
    }

    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }

    public function getEvent(): ModelEvent {
        return $this->event;
    }

    public function getCreatingState(): string {
        return ModelPayment::STATE_NEW;
    }
}
