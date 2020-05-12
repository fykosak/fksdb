<?php

namespace FKSDB\Events\Machine;

use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\TransitionConditionFailedException;
use FKSDB\Events\TransitionOnExecutedException;
use FKSDB\Events\TransitionUnsatisfiedTargetException;
use FKSDB\Logging\ILogger;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @property array onExecuted
 */
class Transition {
    use SmartObject;

    const TYPE_SUCCESS = ILogger::SUCCESS;
    const TYPE_WARNING = ILogger::WARNING;
    const TYPE_DANGEROUS = ILogger::ERROR;
    const TYPE_DEFAULT = 'secondary';

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
     * @var
     */
    private $type;

    /**
     * Transition constructor.
     * @param string $mask
     * @param string $label
     * @param string $type
     */
    function __construct(string $mask, $label = null, string $type = self::TYPE_DEFAULT) {
        $this->setMask($mask);
        $this->label = $label;
        if (!in_array($type, $this->getAllowedBehaviorTypes())) {
            throw new InvalidArgumentException(sprintf('Behavior type %s not allowed', $type));
        }
        $this->type = $type;
    }

    private function getAllowedBehaviorTypes(): array {
        return [
            self::TYPE_SUCCESS,
            self::TYPE_WARNING,
            self::TYPE_DANGEROUS,
            self::TYPE_DEFAULT,
        ];
    }

    /**
     * Meaningless idenifier.
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string {
        if ($this->isTerminating()) {
            return self::TYPE_DANGEROUS;
        }
        if ($this->isCreating()) {
            return self::TYPE_SUCCESS;
        }
        return $this->type;
    }

    /**
     * @param $name
     */
    private function setName(string $name) {
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
     * @param Holder $holder
     * @return bool
     */
    public function isVisible(Holder $holder): bool {
        return $this->getEvaluator()->evaluate($this->visible, $holder);
    }

    /**
     * @param $condition
     */
    public function setCondition($condition) {
        $this->condition = $condition;
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
    private function getEvaluator(): ExpressionEvaluator {
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
        if (!$this->isConditionFulfilled($holder)) {
            return $this;
        }
        return null;
    }

    /**
     * @param Holder $holder
     * @return bool
     */
    private function isConditionFulfilled(Holder $holder) {
        return $this->getEvaluator()->evaluate($this->condition, $holder);
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
        $validator->validate($baseHolder);
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
            $inducedTransition->changeState($holder->getBaseHolder($holderName));
            $inducedTransitions[] = $inducedTransition;
        }

        $this->changeState($holder->getBaseHolder($this->getBaseMachine()->getName()));

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
            foreach ($this->onExecuted as $cb) {
                $cb($this, $holder);
            }
        } catch (\Exception $exception) {
            throw new TransitionOnExecutedException($this->getName(), null, $exception);
        }
    }

    /**
     * @note Assumes the condition is fullfilled.
     * @param BaseHolder $holder
     */
    private function changeState(BaseHolder $holder) {
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
