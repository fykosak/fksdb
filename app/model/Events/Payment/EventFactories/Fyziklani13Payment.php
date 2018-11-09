<?php

namespace Events\Payment\EventFactories;

use Events\Payment\Machine;
use FKSDB\ORM\ModelEventPayment;

class Fyziklani13Payment extends EventTransitionFactory {

    public function createTransitions(Machine &$machine) {
        $options = (object)[
            'bcc' => 'miso@fykos.cz',
            'from' => 'db@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];

        $transition = $this->transitionFactory->createTransition(null, ModelEventPayment::STATE_WAITING, _('Vytvorit platbu'));

        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CONFIRMED, _('Zaplatil'));
        $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', 'michalc@fykos.cz', $options);
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $machine->addTransition($transition);
    }

    public function createMachine(string $state = null): Machine {
        return new Machine($state);
    }
}
