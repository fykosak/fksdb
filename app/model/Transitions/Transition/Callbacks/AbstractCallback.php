<?php

namespace FKSDB\Transitions\Callbacks;

use FKSDB\Transitions\IStateModel;

/**
 * Class AbstractCallback
 * @package FKSDB\Transitions\Callbacks
 */
abstract class AbstractCallback {

    /**
     * @param IStateModel|null $model
     * @param array $args
     * @return void
     */
    public final function __invoke(IStateModel $model = null, ...$args) {
        $this->evaluate($model, ...$args);
    }

    /**
     * @param IStateModel|null $model
     * @param array $args
     * @return void
     */
    abstract protected function evaluate(IStateModel $model = null, ...$args);
}
