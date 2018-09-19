<?php

namespace Events\Machine;

use ArrayAccess;
use ArrayIterator;
use Events\Model\Holder\Holder;
use IteratorAggregate;
use LogicException;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine extends FreezableObject implements ArrayAccess, IteratorAggregate {

    /**
     * @var array of function(ArrayHash $values, Machine $machine)
     */
    public $onSubmit;

    /**
     * @var BaseMachine[]
     */
    private $baseMachines = array();

    /**
     * @var BaseMachine
     */
    private $primaryMachine;

    /**
     * @var Holder
     */
    private $holder;

    public function setPrimaryMachine($name) {
        $this->updating();
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    public function getPrimaryMachine() {
        return $this->primaryMachine;
    }

    public function addBaseMachine(BaseMachine $baseMachine) {
        $this->updating();
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
        $baseMachine->freeze();
    }

    public function getBaseMachine($name) {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }

    public function setHolder(Holder $holder) {
        foreach ($this->baseMachines as $name => $baseMachine) {
            $state = $holder[$name]->getModelState();
            $baseMachine->setState($state);
        }
        $this->holder = $holder;
        if ($holder->getMachine() !== $this) {
            $holder->setMachine($this);
        }
    }

    public function getHolder() {
        return $this->holder;
    }

    /*
     * Syntactic-sugar interfaces
     */

    public function getIterator() {
        return new ArrayIterator($this->baseMachines);
    }

    public function offsetExists($offset) {
        return isset($this->baseMachines[$offset]);
    }

    public function offsetGet($offset) {
        return $this->baseMachines[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseMachine method.');
    }

    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base machine.');
    }

}

