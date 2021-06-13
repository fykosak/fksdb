<?php

namespace FKSDB\Models\Transitions\Transition\Statements;

abstract class Statement {
    /**
     * @param array $args
     * @return bool
     */
    abstract protected function evaluate(...$args): bool;

    /**
     * @param array $args
     * @return bool
     */
    final public function __invoke(...$args): bool {
        return $this->evaluate(...$args);
    }
}
