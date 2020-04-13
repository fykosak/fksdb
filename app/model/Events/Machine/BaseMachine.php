<?php

namespace Events\Machine;

use Nette\FreezableObject;
use Nette\InvalidArgumentException;
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
    const EXECUTABLE = 0x1;
    const VISIBLE = 0x2;

    /**
     * @var string
     */
    private $name;
    /**
     * @var string[]
     */
    private $states;

    /**
     * @var Transition[]
     */
    private $transitions = [];

    /**
     * @var Machine
     */
    private $machine;

    /**
     * BaseMachine constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param $state
     * @param $label
     */
    public function addState($state, $label) {
        $this->states[$state] = $label;
    }

    /**
     * @param Transition $transition
     */
    public function addTransition(Transition $transition) {
        $this->updating();
        $transition->setBaseMachine($this);
        $transition->freeze();

        $this->transitions[$transition->getName()] = $transition;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param $transitionMask
     * @param $induced
     */
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

    /**
     * @return Machine
     */
    public function getMachine() {
        return $this->machine;
    }

    /**
     * @param Machine $machine
     */
    public function setMachine(Machine $machine) {
        $this->machine = $machine;
    }

    /**
     * @return string[]
     */
    public function getStates() {
        return $this->states;
    }

    /**
     * @param string state identification
     * @return string
     */
    public function getStateName(string $state) {
        switch ($state) {
            case self::STATE_INIT:
                return _('vznikající');
            case self::STATE_TERMINATED:
                return _('zaniklý');
            default:
                return _($state);
        }
    }

    /**
     * @return Transition[]
     */
    public function getTransitions() {
        return $this->transitions;
    }

    /**
     * @param int $mode
     * @return Transition[]
     */
    public function getAvailableTransitions($mode = self::EXECUTABLE) {
        return array_filter($this->getMatchingTransitions(), function (Transition $transition) use ($mode) {
            return
                (!($mode & self::EXECUTABLE) || $transition->canExecute()) && (!($mode & self::VISIBLE) || $transition->isVisible());
        });
    }

    /**
     * @param $name
     * @return Transition
     */
    public function getTransition($name) {
        return $this->transitions[$name];
    }

    /**
     * @param $state
     * @return Transition[]
     */
    public function getTransitionByTarget($state) {
        $candidates = array_filter($this->getMatchingTransitions(), function (Transition $transition) use ($state) {
            return $transition->getTarget() == $state;
        });
        if (count($candidates) == 0) {
            return null;
        } elseif (count($candidates) > 1) {
            throw new InvalidArgumentException("Target state '$state' is reachable via multiple edges."); //TODO may this be anytime useful?
        } else {
            return reset($candidates);
        }
    }

    /**
     * @param null $mask
     * @return Transition[]
     */
    private function getMatchingTransitions($mask = null) {
        if ($mask === null) {
            $mask = $this->getMachine()->getHolder()->getBaseHolder($this->name)->getModelState();
        }
        return array_filter($this->transitions, function (Transition $transition) use ($mask) {
            return $transition->matches($mask);
        });
    }

}
