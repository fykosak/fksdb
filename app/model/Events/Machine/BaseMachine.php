<?php

namespace Events\Machine;

use Nette\FreezableObject;
use Nette\InvalidStateException;
use Nette\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseMachine extends FreezableObject {

    const STATE_INIT = '__init';
    const STATE_TERMINATED = '__terminated';
    const STATE_ANY = '*';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string (enum?)
     */
    private $state;

    /**
     * @var string[$state]
     */
    private $states;

    /**
     * @var Transition[$transitionMask]
     */
    private $transitions;

    /**
     * @var Machine
     */
    private $machine;

    public function __construct($name) {
        $this->name = $name;
    }

    public function addState($state, $label) {
        $this->updating();
        $this->states[$state] = $label;
    }

    public function addTransition(Transition $transition) {
        $this->updating();
        $transition->setBaseMachine($this);
        $transition->freeze();

        $this->transitions[$transition->getName()] = $transition;
    }

    public function getName() {
        return $this->name;
    }

    public function addInducedTransition($transitionMask, $induced) {
        if (!$this->isFrozen()) {
            throw new InvalidStateException('Cannot add induced transitions to unfreezed base machine.');
        }
        foreach ($this->getMatchingTransitions($transitionMask) as $transition) {
            foreach ($induced as $machineName => $state) {
                $targetMachine = $this->getMachine()->getBaseMachine($machineName);
                $transition->addInducedTransition($targetMachine, $state);
            }
        }
    }

    public function getMachine() {
        return $this->machine;
    }

    public function setMachine(Machine $machine) {
        $this->machine = $machine;
    }

    /**
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    public function getAvailableTransitions() {
        // a) matches
        // b) condition
        throw new NotImplementedException();
    }

    public function getTransition($name) {
        return $this->transitions[$name];
    }

    public function getTransitionByTarget($state) {
        throw new NotImplementedException();
    }

    private function getMatchingTransitions($mask) {
        return array_filter($this->transitions, function(Transition $transition) use($mask) {
                    return $transition->matches($mask);
                });
    }

}
