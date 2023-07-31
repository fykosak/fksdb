<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Exceptions\TransitionOnExecutedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\SmartObject;

/**
 * @template H of ModelHolder
 */
class Transition
{
    use SmartObject;

    /** @var (callable(H):bool)|bool|null */
    protected $condition;
    public BehaviorType $behaviorType;
    private string $label;
    /**
     * @phpstan-var (Statement<void,H>|callable(H):void)[]
     */
    public array $beforeExecute = [];
    /**
     * @phpstan-var (Statement<void,H>|callable(H):void)[]
     */
    public array $afterExecute = [];

    protected bool $validation;
    /** @var EnumColumn&FakeStringEnum */
    public EnumColumn $source;
    /** @var EnumColumn&FakeStringEnum */
    public EnumColumn $target;

    /**
     * @param EnumColumn&FakeStringEnum $sourceState
     */
    public function setSourceStateEnum(EnumColumn $sourceState): void
    {
        $this->source = $sourceState;
    }

    /**
     * @param EnumColumn&FakeStringEnum $targetState
     */
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

    public function getLabel(): string
    {
        return _($this->label);
    }

    public function label(): string
    {
        return _($this->label);
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label ?? '';
    }

    /**
     * @param callable|bool $condition
     */
    public function setCondition($condition): void
    {
        $this->condition = is_bool($condition) ? fn() => $condition : $condition;
    }

    /**
     * @phpstan-param H $holder
     */
    public function canExecute(ModelHolder $holder): bool
    {
        if (!isset($this->condition)) {
            return true;
        }
        if (is_callable($this->condition)) {
            return (bool)($this->condition)($holder);
        }
        return (bool)$this->condition;
    }

    public function getValidation(): bool
    {
        return $this->validation ?? true;
    }

    public function setValidation(?bool $validation): void
    {
        $this->validation = $validation ?? true;
    }

    /**
     * @phpstan-param (Statement<void,H>|callable(H):void) $callBack
     */
    public function addBeforeExecute(callable $callBack): void
    {
        $this->beforeExecute[] = $callBack;
    }

    /**
     * @phpstan-param (Statement<void,H>|callable(H):void) $callBack
     */
    public function addAfterExecute(callable $callBack): void
    {
        $this->afterExecute[] = $callBack;
    }

    /**
     * @phpstan-param H $holder
     */
    final public function callBeforeExecute(ModelHolder $holder): void
    {
        foreach ($this->beforeExecute as $callback) {
            $callback($holder);
        }
    }

    /**
     * @phpstan-param H $holder
     */
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
