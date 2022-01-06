<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Transitions\Transition\Transition;

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
}
