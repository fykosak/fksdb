<?php

namespace Events\Payment;

use FKSDB\ORM\ModelEventPayment;
use Nette\Diagnostics\Debugger;

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

    public function __construct($state) {
        $this->state = $state;
    }

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
        Debugger::barDump($this->state);
        Debugger::barDump($this->transitions);
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

    public function executeTransition($id, ModelEventPayment $model) {
        $availableTransitions = $this->getAvailableTransitions();
        foreach ($availableTransitions as $transition) {
            if ($transition->getId() === $id) {
                $transition->execute($model);
                return $transition->getToState();
            }
        }
        throw new \Exception(\sprintf(_('Transition %s is not available'), $id));
    }
}
