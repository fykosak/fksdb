<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Statement;

class State implements Statement
{
    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return $holder->getModelState()->value === $this->state;
    }
}
