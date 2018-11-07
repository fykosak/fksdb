<?php

namespace Events\Payment;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {

    /**
     * @var string
     */
    private $state;

    /**
     * @var Transition[]
     */
    private $transitions = [];

    private $initState;

    /**
     * @param Transition $transition
     */
    public function addTransition(Transition $transition) {
        $this->transitions[] = $transition;
    }

    /**
     * @return string
     */
    public function getState(): string {
        return $this->state;
    }

    /**
     * @param $state
     */
    public function setState(string $state) {
        $this->state = $state;
    }

    /**
     * @param string state identification
     * @return string
     */
    public function getStateName(): string {
        return _($this->state);
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array {
        return $this->transitions;
    }

    /**
     * @return Transition[]
     */
    public function getAvailableTransitions(): array {
        return array_filter($this->transitions, function (Transition $transition) {
            return $transition->getFromState() === $this->state;
        });
    }

    public function getInitState() {
        return $this->initState;
    }

    public function setInitState(string $state) {
        $this->initState = $state;
    }
}
