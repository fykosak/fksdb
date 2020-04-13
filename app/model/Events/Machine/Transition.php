<?php

namespace Events\Machine;

use Events\Model\ExpressionEvaluator;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Events\TransitionConditionFailedException;
use Events\TransitionOnExecutedException;
use Events\TransitionUnsatisfiedTargetException;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition {
    use SmartObject;

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
        $this->condition = $condition;
    }

    /**
     * @param $dangerous
     */
    public function setDangerous($dangerous) {
        $this->dangerous = $dangerous;
    }

    /**
     * @param $visible
     */
    public function setVisible($visible) {
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
     * @param Holder $holder
     * @return Transition[]
     */
    private function getInducedTransitions(Holder $holder): array {
        $result = [];
        foreach ($this->inducedTransitions as $baseMachineName => $targetState) {
            $targetMachine = $this->getBaseMachine()->getMachine()->getBaseMachine($baseMachineName);
            $oldState = $holder->getBaseHolder($baseMachineName)->getModelState();
            $inducedTransition = $targetMachine->getTransitionByTarget($oldState, $targetState);
            if ($inducedTransition) {
                $result[$baseMachineName] = $inducedTransition;
            }
        }
        return $result;
    }

    /**
     *
     * @param Holder $holder
     * @return null|Transition
     */
    private function getBlockingTransition(Holder $holder) {
        foreach ($this->getInducedTransitions($holder) as $inducedTransition) {
            if ($inducedTransition->getBlockingTransition($holder)) {
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
     * @param Holder $holder
     * @param Transition[] $inducedTransitions
     * @return bool
     */
    private function validateTarget(Holder $holder, array $inducedTransitions): bool {
        foreach ($inducedTransitions as $inducedTransition) {
            if (($result = $inducedTransition->validateTarget($holder, [])) !== true) { // intentionally =
                return $result;
            }
        }

        $baseHolder = $holder->getBaseHolder($this->getBaseMachine()->getName());
        $validator = $baseHolder->getValidator();
        $validator->validate($baseHolder, $this->getTarget());
        return $validator->getValidationResult();
    }

    /**
     * @param Holder $holder
     * @return bool
     */
    public final function canExecute(Holder $holder) {
        return !$this->getBlockingTransition($holder);
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
     * @param Holder $holder
     * @return array
     * @todo Induction work only for one level.
     */
    public final function execute(Holder $holder) {
        $blockingTransition = $this->getBlockingTransition($holder);
        if ($blockingTransition) {
            throw new TransitionConditionFailedException($blockingTransition);
        }

        $inducedTransitions = [];
        foreach ($this->getInducedTransitions($holder) as $holderName => $inducedTransition) {
            $inducedTransition->_execute($holder->getBaseHolder($holderName));
            $inducedTransitions[] = $inducedTransition;
        }

        $this->_execute($holder->getBaseHolder($this->getBaseMachine()->getName()));

        $validationResult = $this->validateTarget($holder, $inducedTransitions);
        if ($validationResult !== true) {
            throw new TransitionUnsatisfiedTargetException($validationResult);
        }

        return $inducedTransitions;
    }

    /**
     * Triggers onExecuted event.
     *
     * @param Holder $holder
     * @param Transition[] $inducedTransitions
     */
    public final function executed(Holder $holder, $inducedTransitions) {
        foreach ($inducedTransitions as $inducedTransition) {
            $inducedTransition->executed($holder, []);
        }
        try {
            $this->onExecuted($this, $holder);
        } catch (\Exception $exception) {
            throw new TransitionOnExecutedException($this->getName(), null, $exception);
        }
    }

    /**
     * @note Assumes the condition is fullfilled.
     * @param BaseHolder $holder
     */
    private function _execute(BaseHolder $holder) {
        $holder->setModelState($this->getTarget());
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

