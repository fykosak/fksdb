<?php

namespace Events\Machine;

use Nette\FreezableObject;
use Nette\InvalidArgumentException;

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

    /**
     * @var Transition[]
     */
    private $inducedTransitions = array();

    /**
     * @var string
     */
    private $mask;

    /**
     * @var string
     */
    private $label;

    /**
     * @var boolean|callable
     */
    private $condition;

    /**
     * @var array
     */
    public $onExecuted;

    function __construct($mask, $label) {
        $this->mask = $mask;
        $this->label = $label;
    }

    /**
     * Meaningless idenifier.
     * 
     * @return string
     */
    public function getName() {
        return $this->mask;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getBaseMachine() {
        return $this->baseMachine;
    }

    public function setBaseMachine(BaseMachine $baseMachine) {
        $this->updating();
        $this->baseMachine = $baseMachine;
    }

    public function setCondition($condition) {
        $this->updating();
        $this->condition = $condition;
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

    private function canExecute(BaseHolder $holder) {
        //TODO internally checks the condition -- might be needed for transactional behavior with failed induced transitions
    }

    public function execute(BaseHolder $holder) {
        //TODO (set new state in the machine)
        // execute after transition handler
        // throws TransitionConditionFailedException
    }

    public function matches($mask) {
        //TODO
    }

    public static function validateTransition($mask, $states) {
        $parts = explode('->', $mask);
        if (count($parts) != 2) {
            return false;
        }
        list($sources, $target) = $parts;

        $sources = explode('|', $sources);

        foreach ($sources as $source) {
            if (!in_array($source, array_merge($states, array(BaseMachine::STATE_ANY, BaseMachine::STATE_INIT)))) {
                return false;
            }
        }

        if (!in_array($target, array_merge($states, array(BaseMachine::STATE_TERMINATED)))) {
            return false;
        }

        return true;
    }

}

