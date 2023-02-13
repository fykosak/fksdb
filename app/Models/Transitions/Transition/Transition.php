<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Exceptions\TransitionOnExecutedException;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Statement;
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

    /** @var bool */
    protected $validation;

    public EnumColumn $source;
    public EnumColumn $target;
    protected ExpressionEvaluator $evaluator;

    public function setSourceStateEnum(EnumColumn $sourceState): void
    {
        $this->source = $sourceState;
    }

    public function setTargetStateEnum(EnumColumn $targetState): void
    {
        $this->target = $targetState;
    }

    public function isCreating(): bool
    {
        return $this->source->value === 'init' || $this->source->value === Machine::STATE_INIT;
    }

    public function getId(): string
    {
        return str_replace('.', '_', $this->source->value) . '__' . str_replace('.', '_', $this->target->value);
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

    public function setLabel(?string $label): void
    {
        $this->label = $label ?? '';
    }

    public function setCondition(?callable $callback): void
    {
        $this->condition = $callback;
    }

    public function canExecute(ModelHolder $holder): bool
    {
        return (bool)$this->evaluator->evaluate($this->condition ?? fn() => true, $holder);
    }

    public function getValidation(): bool
    {
        return $this->validation ?? true;
    }

    public function setValidation(?bool $validation): void
    {
        $this->validation = $validation ?? true;
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

    final public function callAfterExecute(ModelHolder $holder): void
    {
        try {
            foreach ($this->afterExecute as $callback) {
                $callback($holder);
            }
        } catch (\Throwable $exception) {
            throw new TransitionOnExecutedException($this->getId() . ': ' . $exception->getMessage(), 0, $exception);
        }
    }
}
