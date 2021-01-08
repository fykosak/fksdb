<?php

namespace FKSDB\Models\Events\Machine;

use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {

    /** @var BaseMachine[] */
    private array $baseMachines = [];

    private BaseMachine $primaryMachine;

    public function setPrimaryMachine(string $name): void {
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    public function getPrimaryMachine(): BaseMachine {
        return $this->primaryMachine;
    }

    public function addBaseMachine(BaseMachine $baseMachine): void {
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
