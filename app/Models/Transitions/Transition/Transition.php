<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Statements\Statement;
use Nette\SmartObject;

class Transition
{
    use SmartObject;

    /** @var callable|bool */
    protected $condition;
    public BehaviorType $behaviorType;
    private string $label;
    /** @var Statement[] */
    public array $beforeExecute = [];
    /** @var Statement[] */
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

    public function matchSource(EnumColumn $source): bool
    {
        return $source->value === $this->sourceStateEnum->value;
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

    public function setBehaviorType(BehaviorType $behaviorType): void
    {
        $this->behaviorType = $behaviorType;
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

    protected function isConditionFulfilled(ModelHolder $holder): bool
    {
        return (bool)$this->evaluator->evaluate($this->condition ?? fn() => true, $holder);
    }

    public function canExecute(ModelHolder $holder): bool
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

    final public function callAfterExecute(ModelHolder $holder, ...$args): void
    {
        foreach ($this->afterExecute as $callback) {
            $callback($holder);
        }
    }
}
