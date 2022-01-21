<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements;

abstract class Statement
{

    abstract protected function evaluate(...$args): bool;

    final public function __invoke(...$args): bool
    {
        return $this->evaluate(...$args);
    }
}
