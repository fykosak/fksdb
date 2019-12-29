<?php

namespace FKSDB\Transitions;

use FKSDB\ORM\Models\ModelEvent;

/**
 * Class AbstractTransitionsGenerator
 * @package FKSDB\Transitions
 */
abstract class AbstractTransitionsGenerator {
    protected $transitionFactory;

    /**
     * AbstractTransitionsGenerator constructor.
     * @param TransitionsFactory $transitionFactory
     */
    public function __construct(TransitionsFactory $transitionFactory) {
        $this->transitionFactory = $transitionFactory;
    }

    /**
     * @param Machine $machine
     * @return mixed
     */
    abstract public function createTransitions(Machine &$machine);
}
