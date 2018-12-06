<?php

namespace FKSDB\EventPayment\Transition\Transitions;

use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\EventPayment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\EventPayment\Transition\AbstractTransitionsGenerator;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\Transition;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Nette\DateTime;

class Fyziklani13Payment extends AbstractTransitionsGenerator {
    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;

    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;

    public function __construct(TransitionsFactory $transitionFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculator $priceCalculatorFactory) {
        parent::__construct($transitionFactory);
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function createTransitions(Machine &$machine) {


        $this->addTransitionInitNew($machine);

        $this->addTransitionNewWaiting($machine);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function () {
            return true;
        });
        $machine->addTransition($transition);


        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_RECEIVED, _('Zaplatil'));
        //  $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/confirm', 'michalc@fykos.cz', $options);
        $transition->setCondition(function (ModelEventPayment $eventPayment) {
            return $this->transitionFactory->getConditionDateBetween(new DateTime('2018-01-01 00:00:00'), new DateTime('2019-02-15 00:00:00'))
                && $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);


        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function (ModelEventPayment $eventPayment) {
            $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $machine->addTransition($transition);
    }

    public function createMachine(ModelEvent $event): Machine {
        $machine = new Machine();
        $machine->setSymbolGenerator($this->symbolGeneratorFactory->createGenerator($event));
        $machine->setPriceCalculator($this->priceCalculatorFactory->createCalculator($event));
        return $machine;
    }

    private function addTransitionInitNew(Machine &$machine) {
        $transition = $this->transitionFactory->createTransition(null, ModelEventPayment::STATE_NEW, _('Napočítať cenu'));
        $transition->setCondition(
            function () {
                return $this->transitionFactory->getConditionDateFrom(new DateTime('2018-01-01 00:00:00'));
            });
        $machine->addTransition($transition);
    }

    private function addTransitionNewWaiting(Machine &$machine) {

        $options = (object)[
            'bcc' => 'miso@fykos.cz',
            'from' => 'db@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];

        $transitionNewWaiting = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_WAITING, _('Vytvorit platbu'));
        $transitionNewWaiting->setCondition(function () {
            return true;
        });
        $transitionNewWaiting->setType(Transition::TYPE_SUCCESS);
        $transitionNewWaiting->onExecuteClosures[] = function (ModelEventPayment $modelEventPayment) use ($machine) {
            $modelEventPayment->update($machine->getSymbolGenerator()->create($modelEventPayment));
        };
        $transitionNewWaiting->onExecutedClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', 'michalc@fykos.cz', $options);

        $machine->addTransition($transitionNewWaiting);
    }
}
