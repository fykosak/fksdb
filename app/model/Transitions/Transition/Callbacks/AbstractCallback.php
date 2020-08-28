<?php

namespace FKSDB\Transitions\Callbacks;

/**
 * Class AbstractCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractCallback {

    /**
     * @param array $args
     * @return void
     */
    final public function __invoke(...$args) {
        $this->evaluate(...$args);
    }

    /**
     * @param array $args
     * @return void
     */
    abstract protected function evaluate(...$args);
}
