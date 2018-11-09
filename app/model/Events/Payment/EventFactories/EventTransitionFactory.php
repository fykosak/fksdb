<?php

namespace Events\Payment\EventFactories;

use Events\Payment\Machine;
use Events\Payment\MachineFactory;

abstract class EventTransitionFactory {
    protected $transitionFactory;

    public function __construct(MachineFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    abstract public function createTransitions(Machine &$machine);

    abstract public function createMachine(string $state = null): Machine;
}
