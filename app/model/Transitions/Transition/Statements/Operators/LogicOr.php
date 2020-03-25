<?php

namespace FKSDB\Transitions\Statements\Operators;

use FKSDB\Transitions\Statements\Statement;

/**
 * Class LogicOr
 * @package FKSDB\Transitions\Statements\Operators
 */
class LogicOr extends Statement {
    /**
     * @var callable[]
     */
    private $args;

    /**
     * LogicOr constructor.
     * @param callable[] ...$args
     */
    public function __construct(...$args) {
        $this->args = $args;
    }

    /**
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool {
        foreach ($this->args as $arg) {
            if ($arg(...$args)) {
                return true;
            }
        }
        return false;
    }
}
