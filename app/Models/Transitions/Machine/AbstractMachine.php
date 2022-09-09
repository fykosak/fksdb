<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;

abstract class AbstractMachine
{
    public const STATE_INIT = '__init';
    public const STATE_TERMINATED = '__terminated';
    public const STATE_ANY = '*';
    /** @var Transition[] */
    protected array $transitions = [];

    public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * @throws UnavailableTransitionsException
     */
    public function getTransitionById(string $id): Transition
    {
        $transitions = \array_filter(
            $this->getTransitions(),
            fn(Transition $transition): bool => $transition->getId() === $id
        );
        return $this->selectTransition($transitions);
    }

    /**
     * @param Transition[] $transitions
     * @throws \LogicException
     * @throws UnavailableTransitionsException
     * Protect more that one transition between nodes
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
}
