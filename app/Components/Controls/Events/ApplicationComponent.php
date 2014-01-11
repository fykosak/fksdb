<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Machine\SubmitProcessingException;
use Events\Machine\TransitionConditionFailedException;
use Events\Model\Holder;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationComponent extends Control {

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var Holder
     */
    private $holder;

    function __construct(Machine $machine, Holder $holder) {
        parent::__construct();
        $this->machine = $machine;
        $this->holder = $holder;
    }

    protected function createComponentForm($name) {
        $form = new Form();

        /*
         * Create containers
         */
        foreach ($this->holder as $name => $baseHolder) {
            $baseMachine = $this->machine[$name];
            if (!$baseHolder->isVisible($baseMachine)) {
                continue;
            }
            $container = $baseHolder->createFormContainer($baseMachine);
            $form->addComponent($container, $name);
        }

        /*
         * Create transition buttons
         */
        $primaryMachine = $this->machine->getPrimaryMachine();
        $that = $this;
        foreach ($primaryMachine->getAvailableTransitions() as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            $submit->onClick[] = function(Form $form) use($transitionName, $that) {
                        $that->handleSubmit($form, $transitionName);
                    };
        }

        /*
         * Create save (no transition) button
         */
        //TODO display this button in dependence on modifiable
        if ($primaryMachine->getState() != BaseMachine::STATE_INIT) {
            $submit = $form->addSubmit('save', _('Uložit'));
            $submit->onClick[] = array($this, 'handleSubmit');
        }

        return $form;
    }

    private function handleSubmit(Form $form, $explicitTransitionName = null, $explicitMachineName = null) {
        try {
            $values = $form->getValues();
            $explicitMachine = $explicitMachineName ? $this->machine->getPrimaryMachine() : $this->machine[$explicitMachineName];

            $this->holder->getConnection()->beginTransaction();

            /*
             * Find out transitions
             */
            $newStates = $this->holder->processValues($values);
            $transitions = array();
            foreach ($newStates as $name => $newState) {
                $transitions[$name] = $this->machine[$name]->getTransitionByTarget($newState);
            }

            if ($explicitTransitionName !== null) {
                if (isset($transitions[$explicitMachineName])) {
                    throw new MachineExectionException(sprintf('Collision of explicit transision %s and processing transition %s', $explicitTransitionName, $explicitTransitionName[$explicitMachineName]->getName()));
                }
                $transitions[$explicitMachineName] = $explicitMachine->getTransition($explicitTransitionName);
            }

            foreach ($transitions as $transition) {
                $transition->execute();
            }


            $this->getHandler()->save($values, $this);
        } catch (TransitionConditionFailedException $e) {
            $form->addError($e->getMessage());
        } catch (SubmitProcessingException $e) {
            $form->addError($e->getMessage());
        }
    }

}
