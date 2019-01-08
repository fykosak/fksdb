<?php

namespace FKSDB\Transitions;

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
    private $connection;

    public function __construct(Connection $connection, TransitionsFactory $transitionsFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        $this->transitionsFactory = $transitionsFactory;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
        $this->connection = $connection;
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
            return new Fyziklani13Payment($this->connection,
                $this->transitionsFactory,
                $this->symbolGeneratorFactory,
                $this->priceCalculatorFactory);
        }
        throw new NotImplementedException(_('Not implemented'),501);
    }
}
