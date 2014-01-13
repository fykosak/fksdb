<?php

namespace Events\Model;

use ArrayAccess;
use ArrayIterator;
use Events\Machine\Machine;
use IteratorAggregate;
use LogicException;
use ModelEvent;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Holder extends FreezableObject implements ArrayAccess, IteratorAggregate {

    /**
     * @var IProcessing[]
     */
    public $processings = array();

    /**
     * @var BaseHolder[]
     */
    private $baseHolders = array();

    /**
     * @var BaseHolder
     */
    private $primaryHolder;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelEvent
     */
    private $event;

    function __construct(Connection $connection) {
        $this->connection = $connection;

        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    public function getConnection() {
        return $this->connection;
    }

    public function setPrimaryHolder($name) {
        $this->updating();
        $this->primaryHolder = $this->getBaseHolder($name);
    }

    public function addBaseHolder(BaseHolder $baseHolder) {
        $this->updating();
        $baseHolder->setHolder($this);
        $baseHolder->freeze();

        $name = $baseHolder->getName();
        $this->baseHolders[$name] = $baseHolder;
    }

    public function addProcessing(IProcessing $processing) {
        $this->updating();
        $this->processings[] = $processing;
    }

    public function getBaseHolder($name) {
        if (!array_key_exists($name, $this->baseHolders)) {
            throw new InvalidArgumentException("Unknown base holder '$name'.");
        }
        return $this->baseHolders[$name];
    }

    public function getEvent() {
        return $this->event;
    }

    public function setEvent(ModelEvent $event) {
        $this->updating();
        $this->event = $event;
    }

    public function setModels($models) {
        foreach ($models as $name => $model) {
            $this[$name]->setModel($model);
        }
    }

    public function saveModels() {
        foreach ($this->baseHolders as $name => $baseHolder) {
            $baseHolder->saveModel();
        }
    }

    /**
     * Apply processings to the values and sets them to the ORM model.
     * 
     * @param ArrayHash $values
     * * @param \Events\Model\Machine $machine
     * @return string[] machineName => new state
     */
    public function processFormValues(ArrayHash $values, Machine $machine) {
        $newStates = array();
        foreach ($this->processings as $processing) {
            $result = $processing->process($values, $machine, $this);
            if ($result) {
                $newStates = array_merge($newStates, $result);
            }
        }

        foreach ($this->baseHolders as $name => $baseHolder) {
            if (isset($values[$name])) {
                $baseHolder->updateModel($values[$name]);
            }
        }
        return $newStates;
    }

    /*
     * Syntax-sugar Interfaces
     */

    public function getIterator() {
        return new ArrayIterator($this->baseHolders);
    }

    public function offsetExists($offset) {
        return isset($this->baseHolders[$offset]);
    }

    public function offsetGet($offset) {
        return $this->baseHolders[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseHolder method.');
    }

    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base holder.');
    }

}
