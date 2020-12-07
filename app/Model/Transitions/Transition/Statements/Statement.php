<?php

namespace FKSDB\Model\Transitions\Transition\Statements;

/**
 * Class Statement
 * @author Michal Červeňák <miso@fykos.cz>
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
    final public function __invoke(...$args): bool {
        return $this->evaluate(...$args);
    }
}
