<?php

namespace FKSDB\Transitions;

use FKSDB\ORM\ModelEvent;

abstract class AbstractTransitionsGenerator {
    protected $transitionFactory;

    public function __construct(TransitionsFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    abstract public function createTransitions(Machine &$machine);

    abstract public function createMachine(ModelEvent $event): Machine;
}
