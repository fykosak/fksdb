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

    /** @var callable|bool */
    protected $condition;
    public ?BehaviorType $behaviorType = null;
    private string $label;
    /** @var TransitionCallback[] */
    public array $beforeExecute = [];
    /** @var TransitionCallback[] */
    public array $afterExecute = [];

    public EnumColumn $sourceStateEnum;
    public EnumColumn $targetStateEnum;
    protected ExpressionEvaluator $evaluator;

    public function setSourceStateEnum(EnumColumn $sourceState): void
    {
        $this->sourceStateEnum = $sourceState;
    }

    public function setTargetStateEnum(EnumColumn $targetState): void
    {
        $this->targetStateEnum = $targetState;
    }

    final public function matchSource(EnumColumn $source): bool
    {
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

    public static function createId(EnumColumn $sourceState, EnumColumn $targetState): string
    {
        return $sourceState->value . '__' . $targetState->value;
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

    final public function callBeforeExecute(ModelHolder $holder): void
    {
        foreach ($this->beforeExecute as $callback) {
            $callback($holder);
        }
    }

    final public function callAfterExecute(...$args): void
    {
        foreach ($this->afterExecute as $callback) {
            $callback(...$args);
        }
    }
}
