<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-import-type Enum from Transition
 */
final class TransitionsSelection
{
    /** @phpstan-var Transition<THolder>[] */
    private array $transitions;

    /** @phpstan-param Transition<THolder>[] $transitions */
    public function __construct(array $transitions)
    {
        $this->transitions = $transitions;
    }

    /**
     * @return $this
     * @phpstan-param Enum $target
     */
    public function filterByTarget(EnumColumn $target): self
    {
        $this->transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $target->value === $transition->target->value
        );
        return $this;
    }

    /**
     * @return $this
     * @phpstan-param Enum $source
     */
    public function filterBySource(EnumColumn $source): self
    {
        $this->transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $source->value === $transition->source->value
        );
        return $this;
    }

    /**
     * @return $this
     * @phpstan-param THolder $holder
     */
    public function filterAvailable(ModelHolder $holder): self
    {
        $this->transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $transition->canExecute($holder)
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function filterById(string $id): self
    {
        $this->transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $transition->getId() === $id
        );
        return $this;
    }

    /**
     * @phpstan-return Transition<THolder>
     * Protect more that one transition between nodes
     * @throws UnavailableTransitionsException
     * @throws \LogicException
     */
    public function select(): Transition
    {
        $length = \count($this->transitions);
        if ($length > 1) {
            throw new UnavailableTransitionsException(UnavailableTransitionsException::ReasonLot);
        }
        if (!$length) {
            throw new UnavailableTransitionsException(UnavailableTransitionsException::ReasonNone);
        }
        return \array_values($this->transitions)[0];
    }

    public function count(): int
    {
        return count($this->transitions);
    }

    /** @phpstan-return Transition<THolder>[] */
    public function toArray(): array
    {
        return $this->transitions;
    }
}
