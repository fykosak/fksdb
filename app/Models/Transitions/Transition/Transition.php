<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\FailHandler;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\UI\Title;
use Nette\Database\Explorer;
use Nette\SmartObject;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-type Enum (THolder is \FKSDB\Models\Transitions\Holder\ParticipantHolder
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
    public $condition;
    public BehaviorType $behaviorType;
    public Title $label;
    private ?string $successLabel;
    /** @phpstan-var (callable(THolder,Transition<THolder>):void)[] */
    public array $beforeExecute = [];
    /** @phpstan-var (callable(THolder,Transition<THolder>):void)[] */
    public array $afterExecute = [];

    /** @phpstan-var FailHandler<THolder>[] */
    public array $onFail = [];

    public bool $validation;
    /** @phpstan-var Enum */
    public EnumColumn $source;
    /** @phpstan-var Enum */
    public EnumColumn $target;

    private Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    public function setSuccessLabel(?string $successLabel): void
    {
        $this->successLabel = $successLabel;
    }

    public function getSuccessLabel(): string
    {
        return $this->successLabel ?? _('Transition successful');
    }

    public function getId(): string
    {
        return str_replace('.', '_', $this->source->value) . '__' . str_replace('.', '_', $this->target->value);
    }

    /**
     * @throws UnavailableTransitionException
     * @throws \Throwable
     * @phpstan-param THolder $holder
     */
    final public function execute(ModelHolder $holder): void
    {
        if (!$this->canExecute($holder)) {
            throw new UnavailableTransitionException($this, $holder);
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            foreach ($this->beforeExecute as $callback) {
                $callback($holder, $this);
            }
            $holder->setState($this->target);
            foreach ($this->afterExecute as $callback) {
                $callback($holder, $this);
            }
        } catch (\Throwable $exception) {
            if (!$outerTransition) {
                $this->explorer->getConnection()->rollBack();
            }
            if (count($this->onFail)) {
                foreach ($this->onFail as $failHandler) {
                    $failHandler->handle($exception, $holder, $this);
                }
            } else {
                throw $exception;
            }
        }
        if (!$outerTransition) {
            $this->explorer->getConnection()->commit();
        }
    }

    /**
     * @phpstan-param THolder $holder
     */
    public function canExecute(ModelHolder $holder): bool
    {
        if ($this->source->value !== $holder->getState()->value) {
            return false;
        }
        if (!isset($this->condition)) {
            return true;
        }
        if (is_callable($this->condition)) {
            return (bool)($this->condition)($holder);
        }
        return (bool)$this->condition;
    }
}
