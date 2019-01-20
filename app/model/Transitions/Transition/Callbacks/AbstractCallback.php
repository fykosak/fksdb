<?php

namespace FKSDB\Transitions\Callbacks;

use FKSDB\Transitions\IStateModel;

abstract class AbstractCallback {

    /**
     * @param IStateModel|null $model
     * @return void
     */
    public final function __invoke(IStateModel $model = null) {
        $this->evaluate($model);
    }

    /**
     * @param IStateModel|null $model
     * @return void
     */
    abstract protected function evaluate(IStateModel $model = null);
}
