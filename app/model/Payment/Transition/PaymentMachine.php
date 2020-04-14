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
use Nette\Localization\ITranslator;

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
     * @var ModelEvent
     */
    private $event;
    private $serviceEvent;

    /**
     * PaymentMachine constructor.
     * @param Context $connection
     * @param ServicePayment $servicePayment
     * @param ServiceEvent $serviceEvent
     * @param ITranslator $translator
     */
    public function __construct(Context $connection, ServicePayment $servicePayment, ServiceEvent $serviceEvent, ITranslator $translator) {
        parent::__construct($connection, $servicePayment, $translator);
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param AbstractTransitionsGenerator $factory
     */
    public function setTransitions(AbstractTransitionsGenerator $factory) {
        $factory->createTransitions($this);
    }

    /**
     * @param int $eventId
     */
    public function setEventId(int $eventId) {
        $row = $this->serviceEvent->findByPrimary($eventId);
        $this->event = ModelEvent::createFromActiveRow($row);
    }

    /**
     * @param AbstractSymbolGenerator $abstractSymbolGenerator
     */
    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator) {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }

    /**
     * @param PriceCalculator $priceCalculator
     */
    public function setPriceCalculator(PriceCalculator $priceCalculator) {
        $this->priceCalculator = $priceCalculator;
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
     * @return ModelEvent
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
