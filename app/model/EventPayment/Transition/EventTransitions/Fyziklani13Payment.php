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
        $transition->setForOrgOnly(false);
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_WAITING, _('Vytvorit platbu'));
        $transition->setForOrgOnly(false);
        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', 'michalc@fykos.cz', $options);
        $machine->addTransition($transition);

        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_NEW, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setForOrgOnly(false);
        $machine->addTransition($transition);


        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_RECEIVED, _('Zaplatil'));
        //  $transition->onExecuted[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/confirm', 'michalc@fykos.cz', $options);
        $transition->setForOrgOnly(true);
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);


        $transition = $this->transitionFactory->createTransition(ModelEventPayment::STATE_WAITING, ModelEventPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setForOrgOnly(true);
        $machine->addTransition($transition);
    }

    public function createMachine(string $state = null): Machine {
        return new Machine();
    }
}
