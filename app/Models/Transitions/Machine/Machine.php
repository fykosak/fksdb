<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-import-type Enum from Transition
 */
abstract class Machine
{
    public const STATE_INIT = '__init';
    public const STATE_ANY = '*';

    /** @phpstan-var Transition<THolder>[] */
    protected array $transitions = [];
    protected Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * @phpstan-param Transition<THolder> $transition
     */
    public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @phpstan-return Transition<THolder>[]
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * @throws UnavailableTransitionsException
     * @phpstan-return Transition<THolder>
     */
    public function getTransitionById(string $id): Transition
    {
        $transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $transition->getId() === $id
        );
        return $this->selectTransition($transitions);
    }

    /**
     * @phpstan-param Transition<THolder>[] $transitions
     * @phpstan-return Transition<THolder>
     * Protect more that one transition between nodes
     * @throws UnavailableTransitionsException
     * @throws \LogicException
     */
    protected function selectTransition(array $transitions): Transition
    {
        $length = \count($transitions);
        if ($length > 1) {
            throw new UnavailableTransitionsException();
        }
        if (!$length) {
            throw new UnavailableTransitionsException();
        }
        return \array_values($transitions)[0];
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
     * @phpstan-param THolder $holder
     * @phpstan-return Transition<THolder>[]
     */
    public function getAvailableTransitions(ModelHolder $holder): array
    {
        return \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $this->isAvailable($transition, $holder)
        );
    }

    /**
     * @phpstan-param Enum $source
     * @phpstan-param Enum $target
     * @phpstan-return Transition<THolder>
     */
    public function getTransitionByStates(EnumColumn $source, EnumColumn $target): Transition
    {
        $transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => ($source->value === $transition->source->value) &&
                ($target->value === $transition->target->value)
        );
        return $this->selectTransition($transitions);
    }

    /**
     * @phpstan-param THolder $holder
     * @phpstan-return Transition<THolder>
     */
    final public function getImplicitTransition(ModelHolder $holder): Transition
    {
        return $this->selectTransition($this->getAvailableTransitions($holder));
    }

    /**
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     */
    protected function isAvailable(Transition $transition, ModelHolder $holder): bool
    {
        if ($transition->source->value !== $holder->getModelState()->value) {
            return false;
        }
        return $transition->canExecute($holder);
    }

    /**
     * @throws UnavailableTransitionsException
     * @throws \Throwable
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     */
    public function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$this->isAvailable($transition, $holder)) {
            throw new UnavailableTransitionsException();
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
            $holder->updateState($transition->target);
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
}
