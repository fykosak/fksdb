<?php

namespace FKSDB\EventPayment\Transition\Transitions;

use FKSDB\EventPayment\Transition\AbstractEventTransitions;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\Transition;
use FKSDB\ORM\ModelEventPayment;

class Fyziklani13Payment extends AbstractEventTransitions {

    public function createTransitions(Machine &$machine) {
        $options = (object)[
            'bcc' => 'miso@fykos.cz',
            'from' => 'db@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];


        $transition = $this->transitionFactory->createTransition(null, ModelEventPayment::STATE_NEW, _('Napočítať cenu'));
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_WAITING, _('Vytvorit platbu'));
        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', 'michalc@fykos.cz', $options);
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $machine->addTransition($transition);


        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CONFIRMED, _('Zaplatil'));
        //  $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/confirm', 'michalc@fykos.cz', $options);
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $machine->addTransition($transition);
    }

    public function createMachine(string $state = null): Machine {
        return new Machine($state);
    }
}
