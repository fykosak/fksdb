<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class Transition
{
    use SmartObject;

    /** @var callable */
    protected $condition;
    private ?BehaviorType $behaviorType = null;
    private string $label;
    /** @var TransitionCallback[] */
    public array $beforeExecute = [];
    /** @var TransitionCallback[] */
    public array $afterExecute = [];

    public ?EnumColumn $sourceStateEnum; // null for INIT
    public ?EnumColumn $targetStateEnum; // null for TERMINATED
    protected ExpressionEvaluator $evaluator;

    public function setSourceStateEnum(?EnumColumn $sourceState): void
    {
        $this->sourceStateEnum = $sourceState;
    }

    public function setTargetStateEnum(?EnumColumn $targetState): void
    {
        $this->targetStateEnum = $targetState;
    }

    final public function matchSource(?EnumColumn $source): bool
    {
        if (is_null($source) && is_null($this->sourceStateEnum)) {
            return true;
        }
        if ($source->value === $this->sourceStateEnum->value) {
            return true;
        }
        return false;
    }

    public function isCreating(): bool
    {
        return is_null($this->sourceStateEnum);
    }

    public function isTerminating(): bool
    {
        return is_null($this->sourceStateEnum);
    }

    public function getId(): string
    {
        return static::createId($this->sourceStateEnum, $this->targetStateEnum);
    }

    public static function createId(?EnumColumn $sourceState, ?EnumColumn $targetState): string
    {
        return ($sourceState ? $sourceState->value : 'init') . '__' .
            ($targetState ? $targetState->value : 'terminated');
    }

    public function getBehaviorType(): BehaviorType
    {
        if ($this->isTerminating()) {
            return new BehaviorType(BehaviorType::DANGEROUS);
        }
        if ($this->isCreating()) {
            return new BehaviorType(BehaviorType::SUCCESS);
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
        return (bool)$this->evaluator->evaluate($this->condition ?? fn() => true, ...$args);
    }

    public function canExecute2(ModelHolder $holder): bool
    {
        return $this->isConditionFulfilled($holder);
    }

    public function addBeforeExecute(callable $callBack): void
    {
        $this->beforeExecute[] = $callBack;
    }

    public function addAfterExecute(callable $callBack): void
    {
        $this->afterExecute[] = $callBack;
    }

    final public function callBeforeExecute(ModelHolder $holder, ...$args): void
    {
        foreach ($this->beforeExecute as $callback) {
            $callback($holder, ...$args);
        }
    }

    final public function callAfterExecute(...$args): void
    {
        foreach ($this->afterExecute as $callback) {
            $callback(...$args);
        }
    }
}
