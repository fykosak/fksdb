<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Nette\SmartObject;

class Transition
{
    use SmartObject;

    /** @var callable|bool */
    protected $condition;
    private ?BehaviorType $behaviorType = null;
    private string $label;
    /** @var TransitionCallback[] */
    public array $beforeExecuteCallbacks = [];
    /** @var TransitionCallback[] */
    public array $afterExecuteCallbacks = [];
    public string $sourceState;
    public string $targetState;
    protected ExpressionEvaluator $evaluator;

    public function setSourceState(string $sourceState): void
    {
        $this->sourceState = $sourceState;
    }

    public function matchSource(string $source): bool
    {
        return $this->sourceState === $source || $this->sourceState === AbstractMachine::STATE_ANY;
    }

    public function setTargetState(string $targetState): void
    {
        $this->targetState = $targetState;
    }

    public function isCreating(): bool
    {
        return $this->sourceState === AbstractMachine::STATE_INIT;
    }

    public function isTerminating(): bool
    {
        return $this->targetState === AbstractMachine::STATE_TERMINATED;
    }

    public function getId(): string
    {
        return static::createId($this->sourceState, $this->targetState);
    }

    public static function createId(string $sourceState, string $targetState): string
    {
        return str_replace('*', '_any_', $sourceState) . '__' . $targetState;
    }

    public function getBehaviorType(): BehaviorType
    {
        if ($this->isTerminating()) {
            return new BehaviorType(BehaviorType::TYPE_DANGEROUS);
        }
        if ($this->isCreating()) {
            return new BehaviorType(BehaviorType::TYPE_SUCCESS);
        }
        return $this->behaviorType;
    }

    public function setBehaviorType(string $behaviorType): void
    {
        $this->behaviorType = new BehaviorType($behaviorType);
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

    public function setCondition(?callable $callback): void
    {
        $this->condition = $callback;
    }

    protected function isConditionFulfilled(...$args): bool
    {
        return (bool)$this->evaluator->evaluate($this->condition, ...$args);
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

    final public function callBeforeExecute(ModelHolder $holder, ...$args): void
    {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback($holder, ...$args);
        }
    }

    final public function callAfterExecute(...$args): void
    {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback(...$args);
        }
    }
}
