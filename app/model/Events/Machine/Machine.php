<?php

namespace Events\Machine;

use ArrayAccess;
use ArrayIterator;
use Events\Model\Holder\Holder;
use IteratorAggregate;
use LogicException;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Traversable;

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
        $this->updating();
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
        $this->updating();
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
        $baseMachine->freeze();
    }

    /**
     * @param $name
     * @return BaseMachine
     */
    public function getBaseMachine($name) {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }

    /**
     * @param Holder $holder
     */
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

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }

    /*
     * Syntactic-sugar interfaces
     */

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator() {
        return new ArrayIterator($this->baseMachines);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->baseMachines[$offset]);
    }

    /**
     * @param mixed $offset
     * @return BaseMachine|mixed
     */
    public function offsetGet($offset) {
        return $this->baseMachines[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseMachine method.');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base machine.');
    }

}

