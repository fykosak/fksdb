<?php


namespace FKSDB\EventPayment\Transition;


interface IStateModel {
    public function updateState($newState);

    public function getState();
}
