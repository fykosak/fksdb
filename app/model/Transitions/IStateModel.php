<?php

namespace FKSDB\Transitions;

/**
 * Interface IStateModel
 * @package FKSDB\Transitions
 */
interface IStateModel {
    /**
     * @param $newState
     * @return mixed
     */
    public function updateState($newState);

    /**
     * @return string|null
     */
    public function getState();

    /**
     * @return IStateModel
     */
    public function refresh(): self;
}
