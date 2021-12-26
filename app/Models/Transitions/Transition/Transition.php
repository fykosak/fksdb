<?php

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\Logging\MessageLevel;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use FKSDB\Models\Transitions\Machine\Machine;

class Transition
{
    use SmartObject;


    /** @var callable|bool */
    protected $condition;
    private TransitionBehaviorType $behaviorType = TransitionBehaviorType::DEFAULT;
    private string $label;
    /** @var callable[] */
    public array $beforeExecuteCallbacks = [];
    /** @var callable[] */
    public array $afterExecuteCallbacks = [];
    protected string $sourceState;
    protected string $targetState;
    protected ExpressionEvaluator $evaluator;

    public function setSourceState(string $sourceState): void
    {
        $this->sourceState = $sourceState;
    }

    public function getSourceState(): string
    {
        return $this->sourceState;
    }

    public function matchSource(string $source): bool
    {
        return $this->getSourceState() === $source || $this->getSourceState() === Machine::STATE_ANY;
    }

    public function setTargetState(string $targetState): void
    {
        $this->targetState = $targetState;
    }

    public function getTargetState(): string
    {
        return $this->targetState;
    }

    public function isCreating(): bool
    {
        return $this->sourceState === Machine::STATE_INIT;
    }

    public function isTerminating(): bool
    {
        return $this->getTargetState() === Machine::STATE_TERMINATED;
    }

    public function getId(): string
    {
        return static::createId($this->sourceState, $this->targetState);
    }

    public static function createId(string $sourceState, string $targetState): string
    {
        return str_replace('*', '_any_', $sourceState) . '__' . $targetState;
    }

    public function getBehaviorType(): TransitionBehaviorType
    {
        return $this->behaviorType;
    }

    public function setBehaviorType(?string $behaviorType): void
    {
        $type = TransitionBehaviorType::tryFrom($behaviorType);
        if (!isset($type)) {
            throw new InvalidArgumentException(sprintf('Behavior type %s not allowed', $behaviorType));
        }
        $this->behaviorType = $type;
    }

    protected function getEvaluator(): ExpressionEvaluator
    {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void
    {
        $this->evaluator = $evaluator;
    }

    public function getLabel(): string
    {
        return _($this->label);
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param callable|bool $callback
     */
    public function setCondition($callback): void
    {
        $this->condition = $callback;
    }

    protected function isConditionFulfilled(...$args): bool
    {
        return (bool)$this->getEvaluator()->evaluate($this->condition, ...$args);
    }

    public function canExecute2(ModelHolder $model): bool
    {
        return $this->isConditionFulfilled($model);
    }

    public function addBeforeExecute(callable $callBack): void
    {
        $this->beforeExecuteCallbacks[] = $callBack;
    }

    public function addAfterExecute(callable $callBack): void
    {
        $this->afterExecuteCallbacks[] = $callBack;
    }

    final public function callBeforeExecute(...$args): void
    {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback(...$args);
        }
    }

    final public function callAfterExecute(...$args): void
    {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback(...$args);
        }
    }
}
