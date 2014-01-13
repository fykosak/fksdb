<?php

namespace Events\Model;

use AbstractServiceMulti;
use AbstractServiceSingle;
use ArrayAccess;
use ArrayIterator;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use IteratorAggregate;
use LogicException;
use ModelEvent;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use ORM\IModel;

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
     * @var BaseHolder[]
     */
    private $secondaryBaseHolders = array();

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
        $primaryHolder = $this->primaryHolder = $this->getBaseHolder($name);
        $this->secondaryBaseHolders = array_filter($this->baseHolders, function(BaseHolder $baseHolder) use($primaryHolder) {
                    return $baseHolder !== $primaryHolder;
                });
    }

    public function getPrimaryHolder() {
        return $this->primaryHolder;
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

    public function setModel(IModel $primaryModel = null) {
        $groups = $this->getGroupedSecondaryHolders();

        /*
         * Load each group (common service)
         */
        foreach ($groups as $group) {
            $this->loadSecondaryModels($group, $primaryModel);
        }

        /*
         * Load primary
         */
        $this->primaryHolder->setModel($primaryModel);
    }

    public function saveModels() {
        /*
         * When deleting, first delete children, then parent.
         */
        if ($this->primaryHolder->getModelState() == BaseMachine::STATE_TERMINATED) {
            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
            $this->primaryHolder->saveModel();
        } else {
            /*
             * When creating/updating primary model, propagate its PK to referencinf secondary models.
             */
            $this->primaryHolder->saveModel();
            $primaryModel = $this->primaryHolder->getModel();

            foreach ($this->getGroupedSecondaryHolders() as $holders) {
                $this->updateSecondaryModels($holders, $primaryModel);
            }

            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
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
     * Joined data manipulation
     */

    /**
     * Group non-primary by servise
     * @return BaseHolder[]
     */
    private function getGroupedSecondaryHolders() {
        $groups = array();
        foreach ($this->secondaryBaseHolders as $baseHolder) {
            $key = spl_object_hash($baseHolder->getService());
            if (!isset($groups[$key])) {
                $groups[$key] = array();
            }
            $groups[$key][] = $baseHolder;
        }
        return $groups;
    }

    private function loadSecondaryModels($holders, IModel $primaryModel = null) {
        $reprHandler = reset($holders);
        $service = $reprHandler->getService();
        if ($service instanceof AbstractServiceSingle) {
            $table = $service->getTable();
        } else if ($service instanceof AbstractServiceMulti) {
            /*
             * Assumption that event_participant is'n joined anywhere
             * and that there single-level is-a hierarchy of extendning tables.
             */
            $table = $service->getJoinedService()->getTable();
        }
        $secondary = $primaryModel ? $table->where($reprHandler->getJoinOn(), $primaryModel->getPrimary()) : array();
        $filledHandlers = 0;
        foreach ($secondary as $secondaryModel) {
            //TODO this is terrible refactor (4:16 AM)
            if ($service instanceof AbstractServiceMulti) {
                $mainModel = $secondaryModel->ref($service->getMainService()->getTable()->getName(), $service->getMainService()->getTable()->getPrimary());
                $mainModel = $service->getMainService()->createFromTableRow($mainModel);
                $secondaryModel = $service->composeModel($mainModel, $secondaryModel);
            }
            $holders[$filledHandlers]->setModel($secondaryModel);
            if (++$filledHandlers >= count($holders)) {
                throw new InvalidStateException("More than expected non-primary models loaded for PK '{$primaryModel->getPrimary()}'.");
            }
        }
        for (; $filledHandlers < count($holders); ++$filledHandlers) {
            $holders[$filledHandlers]->setModel(null);
        }
    }

    private function updateSecondaryModels($holders, IModel $primaryModel) {
        $reprHandler = reset($holders);
        $service = $reprHandler->getService();
        foreach ($holders as $holder) {
            $service->updateModel($holder->getModel(), array($holder->getJoinOn() => $primaryModel->getPrimary()));
        }
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
