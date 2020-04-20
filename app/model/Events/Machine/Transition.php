<?php

namespace Events\Machine;

use Events\Model\ExpressionEvaluator;
use Events\Model\Holder\BaseHolder;
use Events\TransitionConditionFailedException;
use Events\TransitionOnExecutedException;
use Events\TransitionUnsatisfiedTargetException;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition extends FreezableObject {

    /** @var BaseMachine */
    private $baseMachine;

    /** @var Transition[] */
    private $inducedTransitions = [];

    /** @var string */
    private $mask;

    /** @var string */
    private $name;

    /** @var string */
    private $target;

    /** @var string */
    private $source;

    /** @var string */
    private $label;

    /** @var boolean|callable */
    private $condition;

    /** @var boolean|callable */
    private $dangerous;

    /** @var boolean|callable */
    private $visible;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @var array
     */
    public $onExecuted = [];

    /**
     * Transition constructor.
     * @param $mask
     * @param $label
     */
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

    /**
     * @param $name
     */
    private function setName($name) {
        // it's used for component naming
        $name = str_replace('*', '_any_', $name);
        $name = str_replace('|', '_or_', $name);
        $this->name = preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getMask() {
        return $this->mask;
    }

    /**
     * @param $mask
     */
    public function setMask($mask) {
        $this->mask = $mask;
        list($this->source, $this->target) = self::parseMask($mask);
        $this->setName($mask);
    }

    /**
     * @return BaseMachine
     */
    public function getBaseMachine() {
        return $this->baseMachine;
    }

    /**
     * @param BaseMachine $baseMachine
     */
    public function setBaseMachine(BaseMachine $baseMachine) {
        $this->updating();
        $this->baseMachine = $baseMachine;
    }

    /**
     * @return string
     */
    public function getTarget(): string {
        return $this->target;
    }

    /**
     * @return string
     */
    public function getSource(): string {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function isCreating(): bool {
        return strpos($this->source, BaseMachine::STATE_INIT) !== false;
    }

    /**
     * @return bool
     */
    public function isTerminating(): bool {
        return $this->target == BaseMachine::STATE_TERMINATED;
    }

    /**
     * @return bool
     */
    public function isDangerous(): bool {
        return $this->isTerminating() || $this->evaluator->evaluate($this->dangerous, $this);
    }

    /**
     * @return bool
     */
    public function isVisible(): bool {
        return $this->evaluator->evaluate($this->visible, $this);
    }

    /**
     * @param $condition
     */
    public function setCondition($condition) {
        $this->updating();
        $this->condition = $condition;
    }

    /**
     * @param $dangerous
     */
    public function setDangerous($dangerous) {
        $this->updating();
        $this->dangerous = $dangerous;
    }

    /**
     * @param $visible
     */
    public function setVisible($visible) {
        $this->updating();
        $this->visible = $visible;
    }

    /**
     * @return ExpressionEvaluator
     */
    public function getEvaluator() {
        return $this->evaluator;
    }

    /**
     * @param ExpressionEvaluator $evaluator
     */
    public function setEvaluator(ExpressionEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    /**
     * @param BaseMachine $targetMachine
     * @param $targetState
     */
    public function addInducedTransition(BaseMachine $targetMachine, $targetState) {
        if ($targetMachine === $this->getBaseMachine()) {
            throw new InvalidArgumentException("Cannot induce transition in the same machine.");
        }
        $targetName = $targetMachine->getName();
        if (isset($this->inducedTransitions[$targetName])) {
            throw new InvalidArgumentException("Induced transition for machine $targetName already defined in " . $this->getName() . ".");
        }
        $this->inducedTransitions[$targetName] = $targetState;
    }

    /**
     * @return array
     */
    private function getInducedTransitions() {
        $result = [];
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
            if ($inducedTransition->getBlockingTransition()) {
                return $inducedTransition;
            }
        }
        if (!$this->isConditionFulfilled()) {
            return $this;
        }
        return null;
    }

    /**
     * @return mixed
     */
    private function isConditionFulfilled() {
        return $this->evaluator->evaluate($this->condition, $this);
    }

    /**
     * @param $inducedTransitions
     * @return bool
     */
    private function validateTarget($inducedTransitions) {
        foreach ($inducedTransitions as $inducedTransition) {
            if (($result = $inducedTransition->validateTarget([])) !== true) { // intentionally =
                return $result;
            }
        }

        $baseHolder = $this->getBaseHolder();
        $validator = $baseHolder->getValidator();
        $validator->validate($baseHolder, $this->getTarget());
        return $validator->getValidationResult();
    }

    /**
     * @return bool
     */
    public final function canExecute() {
        return !$this->getBlockingTransition();
    }

    /**
     * @return bool|callable
     */
    public function getCondition() {
        return $this->condition;
    }

    /**
     * Launch induced transitions and sets new state.
     *
     * @throws TransitionConditionFailedException
     * @todo Induction work only for one level.     *
     */
    public final function execute() {
        if ($blockingTransition = $this->getBlockingTransition()) { // intentionally =
            throw new TransitionConditionFailedException($blockingTransition);
        }

        $inducedTransitions = [];
        foreach ($this->getInducedTransitions() as $inducedTransition) {
            $inducedTransition->_execute();
            $inducedTransitions[] = $inducedTransition;
        }

        $this->_execute();

        $validationResult = $this->validateTarget($inducedTransitions);
        if ($validationResult !== true) {
            throw new TransitionUnsatisfiedTargetException($validationResult);
        }

        return $inducedTransitions;
    }

    /**
     * Triggers onExecuted event.
     *
     * @param Transition[] $inducedTransitions
     */
    public final function executed($inducedTransitions) {
        foreach ($inducedTransitions as $inducedTransition) {
            $inducedTransition->executed([]);
        }
        try {
            $this->onExecuted($this);
        } catch (\Exception $exception) {
            throw new TransitionOnExecutedException($this->getName(), null, $exception);
        }
    }

    /**
     * @note Assumes the condition is fullfilled.
     */
    private function _execute() {
        $this->getBaseMachine()->setState($this->getTarget());
        $this->getBaseHolder()->setModelState($this->getTarget());
    }

    /**
     * @return BaseHolder
     */
    public function getBaseHolder(): BaseHolder {
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

        return preg_match("/(^|\\|){$stateMask}(\\||\$)/", $this->source);
    }

    /**
     * @note Assumes mask is valid.
     *
     * @param string $mask
     * @return array
     */
    private static function parseMask(string $mask): array {
        return explode('->', $mask);
    }

    /**
     * @param string $mask
     * @param array $states
     * @return bool
     */
    public static function validateTransition(string $mask, array $states): bool {
        $parts = self::parseMask($mask);
        if (count($parts) != 2) {
            return false;
        }
        list($sources, $target) = $parts;

        $sources = explode('|', $sources);

        foreach ($sources as $source) {
            if (!in_array($source, array_merge($states, [BaseMachine::STATE_ANY, BaseMachine::STATE_INIT]))) {
                return false;
            }
        }

        if (!in_array($target, array_merge($states, [BaseMachine::STATE_TERMINATED]))) {
            return false;
        }

        return true;
    }

}

