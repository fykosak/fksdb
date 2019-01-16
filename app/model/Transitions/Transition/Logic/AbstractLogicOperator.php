<?php


namespace FKSDB\Transitions\Logic;


use FKSDB\Transitions\IStateModel;

abstract class AbstractLogicOperator {
    /**
     * @param IStateModel|null $model
     * @return bool
     */
    public final function __invoke(IStateModel $model = null): bool {
        return $this->evaluate($model);
    }

    /**
     * @param IStateModel|null $model
     * @return mixed
     */
    abstract protected function evaluate(IStateModel $model = null): bool;
}
