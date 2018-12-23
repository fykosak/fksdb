<?php


namespace FKSDB\Transitions;


interface IStateModel {
    public function updateState($newState);

    public function getState();

    public function refresh(): self;
}
