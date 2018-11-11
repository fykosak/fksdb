<?php

namespace FKSDB\EventPayment\Transition;

abstract class AbstractEventTransitions {
    protected $transitionFactory;

    public function __construct(TransitionsFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    abstract public function createTransitions(Machine &$machine);

    abstract public function createMachine(string $state = null): Machine;
}
