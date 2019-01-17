<?php

namespace FKSDB\Transitions\Logic;

use FKSDB\Transitions\Conditions\AbstractCondition;
use FKSDB\Transitions\IStateModel;

class LogicOr extends AbstractLogicOperator {
    /**
     * @var callable
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
        }
        return $res;
    }
}
