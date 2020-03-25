<?php

namespace FKSDB\Transitions\Statements\Operators;

use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class LogicAnd
 * @package FKSDB\Transitions\Statements\Operators
 */
class LogicAnd extends Statement {
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
     * @param IStateModel|null $model
     * @param array $args
     * @return bool
     */
    protected function evaluate(IStateModel $model = null, ...$args): bool {
        foreach ($this->args as $arg) {
            if (!$arg($model, ...$args)) {
                return false;
            }
        }
        return true;
    }
}
