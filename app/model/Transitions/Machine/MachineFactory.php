<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\Payment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\Payment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\Payment\Transition\Transitions\Fyziklani13Payment;
use Nette\Database\Connection;
use Nette\Localization\ITranslator;
use Nette\NotImplementedException;

/**
 * Class MachineFactory
 * @package FKSDB\Transitions
 */
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
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * MachineFactory constructor.
     * @param ITranslator $translator
     * @param EventAuthorizator $eventAuthorizator
     * @param \ServicePayment $servicePayment
     * @param Connection $connection
     * @param TransitionsFactory $transitionsFactory
     * @param SymbolGeneratorFactory $symbolGeneratorFactory
     * @param PriceCalculatorFactory $priceCalculatorFactory
     */
    public function __construct(ITranslator $translator, EventAuthorizator $eventAuthorizator, \ServicePayment $servicePayment, Connection $connection, TransitionsFactory $transitionsFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        $this->transitionsFactory = $transitionsFactory;
        $this->servicePayment = $servicePayment;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
        $this->connection = $connection;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->translator = $translator;
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
                $this->eventAuthorizator,
                $this->translator
            );
        }
        throw new NotImplementedException(_('Not implemented'), 501);
    }
}
