<?php

namespace Events\Machine;

use Events\Model\Holder\Holder;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {

    /**
     * @var BaseMachine[]
     */
    private $baseMachines = [];

    /**
     * @var BaseMachine
     */
    private $primaryMachine;

    /**
     * @var Holder
     */
    private $holder;

    /**
     * @param $name
     */
    public function setPrimaryMachine($name) {
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    /**
     * @return BaseMachine
     */
    public function getPrimaryMachine() {
        return $this->primaryMachine;
    }

    /**
     * @param BaseMachine $baseMachine
     */
    public function addBaseMachine(BaseMachine $baseMachine) {
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
    }

    /**
     * @param $name
     * @return BaseMachine
     */
    public function getBaseMachine(string $name): BaseMachine {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }

    /**
     * @param Holder $holder
     */
    public function setHolder(Holder $holder) {
        $this->holder = $holder;
        if ($holder->getMachine() !== $this) {
            $holder->setMachine($this);
        }
    }

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }
}
