<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\Payment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\Payment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\Payment\Transition\Transitions\Fyziklani13Payment;
use Nette\Database\Connection;
use Nette\NotImplementedException;

class MachineFactory {
    /**
     * @var TransitionsFactory
     */
    private $transitionsFactory;
    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;
    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var \ServicePayment
     */
    private $servicePayment;
    /**
     * @var EventAuthorizator
     */
    private $eventAuthorizator;

    public function __construct(EventAuthorizator $eventAuthorizator, \ServicePayment $servicePayment, Connection $connection, TransitionsFactory $transitionsFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        $this->transitionsFactory = $transitionsFactory;
        $this->servicePayment = $servicePayment;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
        $this->connection = $connection;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @param ModelEvent $event
     * @return Machine
     */
    public function setUpMachine(ModelEvent $event): Machine {
        $factory = $this->createTransitionsGenerator($event);
        $machine = $factory->createMachine($event);
        $factory->createTransitions($machine);

        return $machine;
    }

    /**
     * @param ModelEvent $event
     * @return AbstractTransitionsGenerator
     * @throws NotImplementedException
     */
    private function createTransitionsGenerator(ModelEvent $event): AbstractTransitionsGenerator {
        if (($event->event_type_id === 1) && ($event->event_year === 13)) {
            return new Fyziklani13Payment($this->servicePayment,
                $this->connection,
                $this->transitionsFactory,
                $this->symbolGeneratorFactory,
                $this->priceCalculatorFactory,
                $this->eventAuthorizator
            );
        }
        throw new NotImplementedException(_('Not implemented'), 501);
    }
}
