<?php

namespace FKSDB\Transitions\Callbacks;

/**
 * Class AbstractCallback
 * @package FKSDB\Transitions\Callbacks
 */
abstract class AbstractCallback {

    /**
     * @param array $args
     * @return void
     */
    public final function __invoke(...$args) {
        $this->evaluate(...$args);
    }

    /**
     * @param array $args
     * @return void
     */
    abstract protected function evaluate(...$args);
}
