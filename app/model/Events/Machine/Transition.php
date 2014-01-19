<?php

namespace Events\Machine;

use Events\TransitionConditionFailedException;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;

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
    private $name;

    /**
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $source;

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
    public $onExecuted = array();

    function __construct($mask, $label) {
        $this->setMask($mask);
        $this->label = $label;
    }

    /**
     * Meaningless idenifier.
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getMask() {
        return $this->mask;
    }

    public function setMask($mask) {
        $this->mask = $mask;
        list($this->source, $this->target) = self::parseMask($mask);
        $this->name = $this->source . '_' . $this->target; // it's used for component naming
    }

    public function getBaseMachine() {
        return $this->baseMachine;
    }

    public function setBaseMachine(BaseMachine $baseMachine) {
        $this->updating();
        $this->baseMachine = $baseMachine;
    }

    public function getTarget() {
        return $this->target;
    }

    public function isCreating() {
        return strpos($this->source, BaseMachine::STATE_INIT) !== false;
    }

    public function setCondition($condition) {
        $this->updating();
        $this->condition = $condition;
    }

    public function addInducedTransition(BaseMachine $targetMachine, $targetState) {
        if ($targetMachine === $this->getBaseMachine()) {
            throw new InvalidArgumentException("Cannot induce transition in the same machine.");
        }
        $targetName = $targetMachine->getName();
        if (isset($this->inducedTransitions[$targetName])) {
            throw new InvalidArgumentException("Induced transition for machine $targetName already defined.");
        }
        $this->inducedTransitions[$targetName] = $targetState;
    }

    private function getInducedTransitions() {
        $result = array();
        foreach ($this->inducedTransitions as $baseMachineName => $targetState) {
            $targetMachine = $this->getBaseMachine()->getMachine()->getBaseMachine($baseMachineName);
            $inducedTransition = $targetMachine->getTransitionByTarget($targetState);
            if ($inducedTransition) {
                $result[] = $inducedTransition;
            }
        }
        return $result;
    }

    /**
     * 
     * @return null|Transition
     */
    private function getBlockingTransition() {
        foreach ($this->getInducedTransitions() as $inducedTransition) {
            if (!$inducedTransition->getBlockingTransition()) {
                return $inducedTransition;
            }
        }
        if (!$this->isConditionFulfilled()) {
            return $this;
        }
        return null;
    }

    private function isConditionFulfilled() {
        if (is_bool($this->condition)) {
            return $this->condition;
        } else if (is_callable($this->condition)) {
            return call_user_func($this->condition);
        } else {
            throw new InvalidStateException("Cannot evaluate condition {$this->condition}.");
        }
    }

    public final function canExecute() {
        return !$this->getBlockingTransition();
    }

    public final function execute() {
        if ($blockingTransition = $this->getBlockingTransition()) { // intentionally =
            throw new TransitionConditionFailedException($blockingTransition);
        }

        foreach ($this->getInducedTransitions() as $inducedTransition) {
            $inducedTransition->_execute();
        }

        $this->_execute();
    }

    /**
     * @note Assumes the condition is fullfilled.
     */
    private function _execute() {
        $this->getBaseMachine()->setState($this->getTarget());
        $this->getBaseHolder()->setModelState($this->getTarget());

        $this->onExecuted($this);
    }

    public function getBaseHolder() {
        return $this->getBaseMachine()->getMachine()->getHolder()->getBaseHolder($this->getBaseMachine()->getName());
    }

    /**
     * @param string $mask It may be either mask of initial state or mask of whole transition.
     * @return boolean
     */
    public function matches($mask) {
        $parts = self::parseMask($mask);

        if (count($parts) == 2 && $parts[1] != $this->getTarget()) {
            return false;
        }
        $stateMask = $parts[0];

        /*
         * Star matches any state but meta-states (initial and terminal) 
         */
        if (strpos(BaseMachine::STATE_ANY, $stateMask) !== false || (strpos(BaseMachine::STATE_ANY, $this->source) !== false &&
                ($mask != BaseMachine::STATE_INIT && $mask != BaseMachine::STATE_TERMINATED))) {
            return true;
        }

        return preg_match("/(^|\\|){$stateMask}(\\||\$)/", $this->source); //TODO verify
    }

    /**
     * @note Assumes mask is valid.
     * 
     * @param string $mask
     */
    private static function parseMask($mask) {
        return explode('->', $mask);
    }

    public static function validateTransition($mask, $states) {
        $parts = self::parseMask($mask);
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

