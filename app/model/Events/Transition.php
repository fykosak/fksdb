<?php

namespace Events;

use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition extends FreezableObject {

    /**
     * @var BaseMachine
     */
    private $baseMachine;
    private $inducedTransitions = array();

    public function __construct() {
        //TODO;
    }

    /**
     * Meaningless idenifier.
     * 
     * @return string
     */
    public function getName() {
        return null;
    }

    public function getLabel() {
        return null; //TODO
    }

    public function getBaseMachine() {
        return $this->baseMachine;
    }

    public function addInducedTransition(BaseMachine $targetMachine, $targetState) {
        if ($targetMachine === $this->getBaseMachine()) {
            throw new InvalidArgumentException("Cannot induce transition in the same machine.");
        }
        $inducedTransition = $targetMachine->getTransitionByTarget($targetState);
        if (!$inducedTransition) {
            trigger_error("Transition " . $this . " induced empty transition in " . $targetMachine . ".", E_USER_WARNING);
        } else {
            $this->inducedTransitions[] = $inducedTransition;
        }
    }

    private function canExecute() {
        //TODO internally checks the condition -- might be needed for transactional behavior with failed induced transitions
    }

    public function execute() {
        //TODO (set new state in the machine)
        // execute after transition handler
        // throws TransitionConditionFailedException
    }

}

class TransitionConditionFailedException extends RuntimeException {
    
}