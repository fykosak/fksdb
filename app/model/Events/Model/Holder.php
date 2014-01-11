<?php

namespace Events\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;
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

    function __construct(Connection $connection) {
        $this->connection = $connection;
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
        $name = $baseHolder->getName();
        $this->baseHolders[$name] = $baseHolder;
        $baseHolder->freeze();
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
     * @return string[] machineName => new state
     */
    public function processFormValues(ArrayHash $values) {
        $newStates = array();
        foreach ($this->processings as $processing) {
            $newStates = array_merge($newStates, $processing->process($this, $values));
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
