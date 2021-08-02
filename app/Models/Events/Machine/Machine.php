<?php

namespace FKSDB\Models\Events\Machine;

use Nette\InvalidArgumentException;

class Machine
{

    /** @var BaseMachine[] */
    private array $baseMachines = [];

    private BaseMachine $primaryMachine;

    public function getPrimaryMachine(): BaseMachine
    {
        return $this->primaryMachine;
    }

    public function setPrimaryMachine(string $name): void
    {
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    public function getBaseMachine(string $name): BaseMachine
    {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }

    public function addBaseMachine(BaseMachine $baseMachine): void
    {
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
    }
}
