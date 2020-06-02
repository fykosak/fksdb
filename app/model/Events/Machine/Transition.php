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

    public const TYPE_SUCCESS = ILogger::SUCCESS;
    public const TYPE_WARNING = ILogger::WARNING;
    public const TYPE_DANGEROUS = ILogger::ERROR;
    public const TYPE_DEFAULT = 'secondary';

    private BaseMachine $baseMachine;

    /** @var Transition[] */
    private array $inducedTransitions = [];

    private string $mask;

    private string $name;

    private string $target;

    private string $source;

    private ?string $label;

    /** @var bool|callable */
    private $condition;

    /** @var bool|callable */
    private $visible;

    private ExpressionEvaluator $evaluator;

    public array $onExecuted = [];

    private string $type;

    /**
     * Transition constructor.
     * @param string $mask
     * @param string|null $label
     * @param string $type
     */
    public function __construct(string $mask, ?string $label, string $type) {
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

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        if ($this->isTerminating()) {
            return self::TYPE_DANGEROUS;
        }
        if ($this->isCreating()) {
            return self::TYPE_SUCCESS;
        }
        return $this->type;
    }

    private function setName(string $name): void {
        // it's used for component naming
        $name = str_replace('*', '_any_', $name);
        $name = str_replace('|', '_or_', $name);
        $this->name = preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function getMask(): string {
        return $this->mask;
    }

    public function setMask(string $mask): void {
        $this->mask = $mask;
        [$this->source, $this->target] = self::parseMask($mask);
        $this->setName($mask);
    }

    public function getBaseMachine(): BaseMachine {
        return $this->baseMachine;
    }

    public function setBaseMachine(BaseMachine $baseMachine): void {
        $this->baseMachine = $baseMachine;
    }

    public function getTarget(): string {
        return $this->target;
    }

    public function getSource(): string {
        return $this->source;
    }

    public function isCreating(): bool {
        return strpos($this->source, BaseMachine::STATE_INIT) !== false;
    }

    public function isTerminating(): bool {
        return $this->target == BaseMachine::STATE_TERMINATED;
    }

    public function isVisible(Holder $holder): bool {
        return $this->getEvaluator()->evaluate($this->visible, $holder);
    }

    /**
     * @param bool|callable $condition
     */
    public function setCondition($condition): void {
        $this->condition = $condition;
    }

    /**
     * @param bool|callable $visible
     */
    public function setVisible($visible): void {
        $this->visible = $visible;
    }

    private function getEvaluator(): ExpressionEvaluator {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }

    public function addInducedTransition(BaseMachine $targetMachine, string $targetState): void {
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

    private function isConditionFulfilled(Holder $holder): bool {
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

    final public function canExecute(Holder $holder): bool {
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
    final public function execute(Holder $holder): array {
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
    final public function executed(Holder $holder, array $inducedTransitions): void {
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
    private function changeState(BaseHolder $holder): void {
        $holder->setModelState($this->getTarget());
    }

    /**
     * @param string $mask It may be either mask of initial state or mask of whole transition.
     * @return bool
     */
    public function matches(string $mask): bool {
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

    public static function validateTransition(string $mask, array $states): bool {
        $parts = self::parseMask($mask);
        if (count($parts) != 2) {
            return false;
        }
        [$sources, $target] = $parts;

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
