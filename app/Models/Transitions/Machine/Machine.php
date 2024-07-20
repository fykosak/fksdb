<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionException;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-import-type Enum from Transition
 */
abstract class Machine
{
    /** @phpstan-var Transition<THolder>[] */
    public array $transitions = [];
    protected Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * @phpstan-param Transition<THolder> $transition
     */
    final public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @phpstan-param TransitionsDecorator<THolder>|null $decorator
     */
    final public function decorateTransitions(?TransitionsDecorator $decorator): void
    {
        if ($decorator) {
            $decorator->decorate($this);
        }
    }

    /**
     * @throws UnavailableTransitionException
     * @throws \Throwable
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     */
    public function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$this->isAvailable($transition, $holder)) {
            throw new UnavailableTransitionException($transition, $holder);
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
            $holder->setState($transition->target);
            $transition->callAfterExecute($holder);
        } catch (\Throwable $exception) {
            if (!$outerTransition) {
                $this->explorer->getConnection()->rollBack();
            }
            throw $exception;
        }
        if (!$outerTransition) {
            $this->explorer->getConnection()->commit();
        }
    }

    /**
     * @phpstan-return THolder
     */
    abstract public function createHolder(Model $model): ModelHolder;

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param Transition<SHolder>[] $transitions
     * @phpstan-return Transition<SHolder>
     * Protect more that one transition between nodes
     * @throws UnavailableTransitionsException
     * @throws \LogicException
     */
    public static function selectTransition(array $transitions): Transition
    {
        $length = \count($transitions);
        if ($length > 1) {
            throw new UnavailableTransitionsException(UnavailableTransitionsException::ReasonLot);
        }
        if (!$length) {
            throw new UnavailableTransitionsException(UnavailableTransitionsException::ReasonNone);
        }
        return \array_values($transitions)[0];
    }

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param SHolder $holder
     * @phpstan-param Transition<SHolder> $transition
     */
    public static function isAvailable(Transition $transition, ModelHolder $holder): bool
    {
        if ($transition->source->value !== $holder->getState()->value) {
            return false;
        }
        return $transition->canExecute($holder);
    }

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param Transition<SHolder>[] $transitions
     * @phpstan-param Enum $target
     * @phpstan-return Transition<SHolder>[]
     */
    public static function filterByTarget(array $transitions, EnumColumn $target): array
    {
        return \array_filter(
            $transitions,
            fn(Transition $transition): bool => $target->value === $transition->target->value
        );
    }

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param Transition<SHolder>[] $transitions
     * @phpstan-param Enum $source
     * @phpstan-return Transition<SHolder>[]
     */
    public static function filterBySource(array $transitions, EnumColumn $source): array
    {
        return \array_filter(
            $transitions,
            fn(Transition $transition): bool => $source->value === $transition->source->value
        );
    }

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param Transition<SHolder>[] $transitions
     * @phpstan-param SHolder $holder
     * @phpstan-return Transition<SHolder>[]
     */
    public static function filterAvailable(array $transitions, ModelHolder $holder): array
    {
        return \array_filter(
            $transitions,
            fn(Transition $transition): bool => self::isAvailable($transition, $holder)
        );
    }

    /**
     * @template SHolder of ModelHolder
     * @phpstan-param Transition<SHolder>[] $transitions
     * @phpstan-return Transition<SHolder>[]
     */
    public static function filterById(array $transitions, string $id): array
    {
        return \array_filter(
            $transitions,
            fn(Transition $transition): bool => $transition->getId() === $id
        );
    }
}
