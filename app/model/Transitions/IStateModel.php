<?php

namespace FKSDB\Transitions;

interface IStateModel {
    public function updateState($newState);

    /**
     * @return string|null
     */
    public function getState();
}
