<?php

namespace FKSDB\Transitions\Conditions;

use FKSDB\Transitions\IStateModel;

abstract class AbstractCondition {
    /**
     * @param IStateModel? $model
     * @return bool
     */
    abstract public function evaluate($model = null): bool;

    /**
     * @param IStateModel? $model
     * @return bool
     */
    public final function __invoke($model = null): bool {
        return $this->evaluate($model);
    }

}
