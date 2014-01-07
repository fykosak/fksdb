<?php

namespace Events\Machine;

use Nette\Forms\Container;
use Nette\FreezableObject;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseMachine extends FreezableObject {

    const STATE_INIT = '__init';
    const STATE_TERMINATED = '__terminated';
    const STATE_ANY = '*';

    private $name;
    private $required;

    public function __construct() {
        //TODO
    }

    public function addState($state, $name) {
        $this->updating();
        //TODO
    }

    public function addTransition(Transition $transition) {
        $this->updating();
        $transition->setBaseMachine($this); // here it should be checked and freezed
        $transition->freeze(); 
        //TODO
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getRequired() {
        return $this->required;
    }

    public function setRequired($required) {//TODO freezing/ctor
        $this->required = $required;
    }

    public function addInducedTransition($transitionMask, $induced) {
        if (!$this->isFrozen()) {
            throw new InvalidStateException('Cannot induce transitions from unfreezed base machine.');
        }
        //TODO
    }

    /**
     * @return string
     */
    public function getState() {
        
    }

    public function getAvailableTransitions() {
        
    }

    public function getTransition($name) {
        //TODO
    }

    public function getTransitionByTarget($state) {
        //TODO, from the current state
    }

    /**
     * @return Container
     */
    public function createFormContainer() {
        
    }

}
