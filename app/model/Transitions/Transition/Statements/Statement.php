<?php

namespace FKSDB\Transitions\Statements;

/**
 * Class Statement
 * @package FKSDB\Transitions\Statements
 */
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
    public final function __invoke(...$args): bool {
        return $this->evaluate(...$args);
    }
}
