<?php

namespace FKSDB\Transitions\Statements;

use FKSDB\Transitions\IStateModel;

abstract class Statement {
    /**
     * @param IStateModel? $model
     * @return bool
     */
    abstract protected function evaluate(IStateModel $model = null): bool;

    /**
     * @param IStateModel? $model
     * @return bool
     */
    public final function __invoke(IStateModel $model = null): bool {
        return $this->evaluate($model);
    }
}
