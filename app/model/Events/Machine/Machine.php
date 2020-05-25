<?php

namespace FKSDB\Events\Machine;

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
     * @param string $name
     * @return void
     */
    public function setPrimaryMachine(string $name) {
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    public function getPrimaryMachine(): BaseMachine {
        return $this->primaryMachine;
    }

    /**
     * @param BaseMachine $baseMachine
     * @return void
     */
    public function addBaseMachine(BaseMachine $baseMachine) {
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
    }

    public function getBaseMachine(string $name): BaseMachine {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }
}
