<?php

namespace FKSDB\Transitions\Statements;

use FKSDB\Transitions\IStateModel;

/**
 * Class Statement
 * @package FKSDB\Transitions\Statements
 */
abstract class Statement {
    /**
     * @param IStateModel? $model
     * @param array $args
     * @return bool
     */
    abstract protected function evaluate(IStateModel $model = null, ...$args): bool;

    /**
     * @param IStateModel? $model
     * @param array $args
     * @return bool
     */
    public final function __invoke(IStateModel $model = null, ...$args): bool {
        return $this->evaluate($model, ...$args);
    }
}
