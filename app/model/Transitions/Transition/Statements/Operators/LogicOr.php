<?php

namespace FKSDB\Transitions\Statements\Operators;

use FKSDB\Transitions\IStateModel;
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
     * @param mixed ...$args
     */
    public function __construct(...$args) {
        $this->args = $args;
    }

    /**
     * @param IStateModel|null $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        $res = false;
        foreach ($this->args as $arg) {
            $res = $arg($model) || $res;
            if ($res) {
                return true;
            }
        }
        return $res;
    }
}
