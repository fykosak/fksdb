<?php

namespace FKSDB\EventPayment\Transition\Transitions;

use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\EventPayment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\EventPayment\Transition\AbstractTransitionsGenerator;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\PaymentMachine;
use FKSDB\EventPayment\Transition\Transition;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\BadRequestException;
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

    public function __construct(TransitionsFactory $transitionFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        parent::__construct($transitionFactory);
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function createTransitions(Machine &$machine) {
        if (!$machine instanceof PaymentMachine) {
            throw new BadRequestException('Očakvaná sa trieda PaymentMachine');
        }

        $this->addTransitionInitNew($machine);
        $this->addTransitionNewWaiting($machine);
        $this->addTransitionNewCanceled($machine);
        $this->addTransitionWaitingReceived($machine);
        $this->addTransitionWaitingCancel($machine);
    }

    public function createMachine(ModelEvent $event): Machine {
        $machine = new PaymentMachine();
        $machine->setSymbolGenerator($this->symbolGeneratorFactory->createGenerator($event));
        $machine->setPriceCalculator($this->priceCalculatorFactory->createCalculator($event));
        return $machine;
    }

    private function addTransitionInitNew(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(null, ModelEventPayment::STATE_NEW, _('Pokračovať k vytvoreniu platby'));
        $transition->setCondition(
            function () {
                return $this->transitionFactory->getConditionDateFrom(new DateTime('2018-01-01 00:00:00'));
            });
        $machine->addTransition($transition);
    }

    private function addTransitionNewWaiting(PaymentMachine &$machine) {

        $options = (object)[
            'bcc' => 'miso@fykos.cz',
            'from' => 'db@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];
        $transition = $this->transitionFactory->createTransition(
            ModelEventPayment::STATE_NEW,
            ModelEventPayment::STATE_WAITING,
            _('Potvrdiť platbu a napočítať cenu')
        );

        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->setCondition(function (ModelEventPayment $eventPayment) {
            return $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit') ||
                $this->transitionFactory->getConditionOwnerAssertion($eventPayment->getPerson());
        });
        $transition->onExecuteClosures[] = function (ModelEventPayment $modelEventPayment) use ($machine) {
            $modelEventPayment->update($machine->getSymbolGenerator()->create($modelEventPayment));
            $modelEventPayment->updatePrice($machine->getPriceCalculator());
        };
        $transition->onExecutedClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', $options);

        $machine->addTransition($transition);
    }

    private function addTransitionNewCanceled(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function () {
            return true;
        });
        $machine->addTransition($transition);
    }

    private function addTransitionWaitingReceived(PaymentMachine &$machine) {
        $options = [];
        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_RECEIVED, _('Zaplatil'));
        //$transition->onExecutedClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/confirm', $options);
        $transition->setCondition(function (ModelEventPayment $eventPayment) {
            return $this->transitionFactory->getConditionDateBetween(new DateTime('2018-01-01 00:00:00'), new DateTime('2019-02-15 00:00:00'))
                && $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
    }

    private function addTransitionWaitingCancel(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function (ModelEventPayment $eventPayment) {
            $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $machine->addTransition($transition);
    }
}
