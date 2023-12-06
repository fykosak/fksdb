<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Exceptions\TransitionOnExecutedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\UI\Title;
use Nette\SmartObject;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-type Enum (THolder is \FKSDB\Models\Events\Model\Holder\BaseHolder
 * ? \FKSDB\Models\ORM\Models\EventParticipantStatus
 * :(THolder is \FKSDB\Models\Transitions\Holder\PaymentHolder
 *     ? \FKSDB\Models\ORM\Models\PaymentState
 *     : (THolder is \FKSDB\Models\Transitions\Holder\TeamHolder
 *     ? \FKSDB\Models\ORM\Models\Fyziklani\TeamState
 *     : (\FKSDB\Models\Utils\FakeStringEnum&EnumColumn)
 *      )
 *     )
 * )
 */
class Transition
{
    use SmartObject;

    /** @phpstan-var (callable(THolder):bool)|bool|null */
    protected $condition;
    public BehaviorType $behaviorType;
    private Title $label;
    private string $successLabel;
    /** @phpstan-var (callable(THolder,Transition<THolder>):void)[] */
    public array $beforeExecute = [];
    /** @phpstan-var (callable(THolder,Transition<THolder>):void)[] */
    public array $afterExecute = [];

    protected bool $validation;
    /** @phpstan-var Enum */
    public EnumColumn $source;
    /** @phpstan-var Enum */
    public EnumColumn $target;

    /**
     * @phpstan-param Enum $sourceState
     */
    public function setSourceStateEnum(EnumColumn $sourceState): void
    {
        $this->source = $sourceState;
    }

    public function setSuccessLabel(string $successLabel): void
    {
        $this->successLabel = $successLabel;
    }

    public function getSuccessLabel(): string
    {
            $this->successLabel ?? _('Transition successful');
    }

    /**
     * @phpstan-param Enum $targetState
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

    public function label(): Title
    {
        return $this->label;
    }

    public function setLabel(string $label, string $icon): void
    {
        $this->label = new Title(null, $label, $icon);
    }

    /**
     * @phpstan-param (callable(THolder):bool)|bool $condition
     */
    public function setCondition($condition): void
    {
        $this->condition = is_bool($condition) ? fn() => $condition : $condition;
    }

    /**
     * @phpstan-param THolder $holder
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
     * @phpstan-param (callable(THolder,Transition<THolder>):void) $callBack
     */
    public function addBeforeExecute(callable $callBack): void
    {
        $this->beforeExecute[] = $callBack;
    }

    /**
     * @phpstan-param (callable(THolder,Transition<THolder>):void) $callBack
     */
    public function addAfterExecute(callable $callBack): void
    {
        $this->afterExecute[] = $callBack;
    }

    /**
     * @phpstan-param THolder $holder
     */
    final public function callBeforeExecute(ModelHolder $holder): void
    {
        foreach ($this->beforeExecute as $callback) {
            $callback($holder, $this);
        }
    }

    /**
     * @phpstan-param THolder $holder
     */
    final public function callAfterExecute(ModelHolder $holder): void
    {
        try {
            foreach ($this->afterExecute as $callback) {
                $callback($holder, $this);
            }
        } catch (\Throwable $exception) {
            throw new TransitionOnExecutedException($this->getId() . ': ' . $exception->getMessage(), 0, $exception);
        }
    }
}
